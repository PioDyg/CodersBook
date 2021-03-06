<?php

namespace CodersLab\CodersBookBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use CodersLab\CodersBookBundle\Entity\Person;
use CodersLab\CodersBookBundle\Entity\CLGroup;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Route("/person")
 */
class PersonController extends Controller {

    private function fileHandle($file, $person, $type) {
        $dir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/';

        if (!$file)
            return;

        switch ($type) {
            case 'cv':
                $fileName = $person->getCvFN();
                break;
            case 'image':
                $fileName = $person->getImageFN();
                break;
        }

        if (!empty($fileName) && file_exists($dir . $fileName)) {
            unlink($dir . $fileName);
        }
        $fileName = md5(uniqid()) . '.' . $file->guessExtension();
        $file->move($dir, $fileName);

        switch ($type) {
            case 'cv':
                $person->setCvFN($fileName);
                break;
            case 'image':
                $person->setImageFN($fileName);
                break;
        }
    }

    private function upload() {


        $form = $this->createFormBuilder()
                ->add('cv', 'file', ['label' => 'Twoje CV', 'required' => false])
                ->add('image', 'file', ['label' => 'Twoje zdjęcie', 'required' => false])
                ->add('save', 'submit', ['label' => 'Wyślij plik'])
                ->getForm();

        return $form;
    }

    private function personForm($person) {


        $form = $this->createFormBuilder($person)
                ->setAction($this->generateUrl('person_admin_create'))
                ->add('name', 'text', ['label' => 'Imię i nazwisko'])
                ->add('email', 'text', ['label' => 'Adres e-mail', 'required' => false])
                ->add('phone', 'text', ['label' => 'Numer telefonu', 'required' => false])
                ->add('github', 'text', ['label' => 'Login Github', 'required' => false])
                ->add('linkedin', 'text', ['label' => 'ID profilu LinkedIn', 'required' => false])
                ->add('clGroup', 'entity', [
                    'label' => 'Grupa',
                    'class' => 'CodersBookBundle:CLGroup',
                    'choice_label' => 'name'])
                ->add('lookingForJob', 'checkbox', ['label' => 'Status zatrudnienia (szuka pracy)', 'required' => false])
                ->add('save', 'submit', ['label' => 'Dodaj osobę'])
                ->getForm();
        return $form;
    }

    private function updatePersonForm($person) {

        $form = $this->createFormBuilder($person)
                ->add('name', 'text', ['label' => 'Imię i nazwisko'])
                ->add('email', 'text', ['label' => 'Adres e-mail', 'required' => false])
                ->add('phone', 'text', ['label' => 'Numer telefonu', 'required' => false])
                ->add('github', 'text', ['label' => 'Login Github', 'required' => false])
                ->add('linkedin', 'text', ['label' => 'ID profilu LinkedIn', 'required' => false])
                ->add('clGroup', 'entity', [
                    'label' => 'Grupa',
                    'class' => 'CodersBookBundle:CLGroup',
                    'choice_label' => 'name'])
                ->add('lookingForJob', 'checkbox', ['label' => 'Status zatrudnienia (szuka pracy)', 'required' => false])
                ->add('save', 'submit', ['label' => 'Zapisz zmiany'])
                ->getForm();
        return $form;
    }

    /**
     * @Route("/all/{name}", name = "person_admin_all")
     * @Template()
     */
    public function showAllPersonsAction($name) {
        $repo = $this->getDoctrine()->getRepository('CodersBookBundle:Person');
        $repoGroup = $this->getDoctrine()->getRepository('CodersBookBundle:CLGroup');

        $group = $repoGroup->findOneByName($name);
        if (!$group) {
            return [
                'error' => 'Nie ma takiej grupy'
            ];
        }
        $persons = $repo->findBy(['clGroup' => $group]);

        return [
            'persons' => $persons,
            'clGroup' => $group
        ];
    }

    /**
     * @Route("/admin/create", name = "person_admin_create")
     * @Template()
     */
    public function createPersonAction(Request $req) {
        $repo = $this->getDoctrine()->getRepository('CodersBookBundle:Person');
        $person = new Person();

        $form = $this->personForm($person);
        $form->handleRequest($req);

        if ($form->isSubmitted()) {
            if ($repo->findByName($person->getName()) || $person->getName() == '') {
                return [
                    'error' => 'Taka osoba już istnieje lub formularz jest pusty!'
                ];
            }
            if (!$person->getClGroup()) {
                return [
                    'error' => 'Nie wybrano grupy!'
                ];
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($person);
            $em->flush();

            return [
                'person' => $person
            ];
        }
    }

    /**
     * @Route("/admin/new", name = "person_admin_new")
     * @Template()
     */
    public function newPersonAction() {
        $person = new Person();

        $form = $this->personForm($person);

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/admin/delete/{id}", name = "person_admin_delete")
     * @Template()
     */
    public function deletePersonAction($id) {
        $repo = $this->getDoctrine()->getRepository('CodersBookBundle:Person');
        $em = $this->getDoctrine()->getManager();
        $deletedPerson = $repo->find($id);

        if ($deletedPerson) {
            $em->remove($deletedPerson);
            $em->flush();
        }

        return [
            'deletedPerson' => $deletedPerson
        ];
    }

    /**
     * @Route("/admin/update/{id}", name = "person_admin_update")
     * @Method("GET")
     * @Template()
     */
    public function updatePersonGetAction($id) {
        $repo = $this->getDoctrine()->getRepository('CodersBookBundle:Person');

        $person = $repo->find($id);
        if (!$person) {
            return [
                'error' => 'Wystąpił błąd brak takiej osoby w bazie danych!'
            ];
        }
        $form = $this->updatePersonForm($person);
        return[
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/admin/update/{id}", name = "person_admin_save")
     * @Method("POST")
     * @Template("CodersBookBundle:Person:updatePersonGet.html.twig")
     */
    public function updatePersonPostAction(Request $req, $id) {
        $repo = $this->getDoctrine()->getRepository('CodersBookBundle:Person');
        $person = $repo->find($id);
        $form = $this->updatePersonForm($person, $person->getId());
        $form->handleRequest($req);

        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($person);
            $em->flush();
        }
        return [
            'form' => $form->createView(),
            'success' => true
        ];
    }

    /**
     * @Route("/admin/upload/{id}", name = "person_admin_upload")
     * @Template()
     */
    public function uploadPersonAction(Request $req, $id) {
        $repo = $this->getDoctrine()->getRepository('CodersBookBundle:Person');
        $person = $repo->find($id);

        $form = $this->upload();
        $form->handleRequest($req);

        if ($form->isSubmitted()) {
            $cv = $form->get('cv')->getData();
            $image = $form->get('image')->getData();

            $this->fileHandle($cv, $person, 'cv');
            $this->fileHandle($image, $person, 'image');

            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }


        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/export_csv/{id}", name = "person_export_csv")
     */
    public function generateCsvAction($id) {

        $response = new StreamedResponse();
        $response->setCallback(function() use($id) {
            $handle = fopen('php://output', 'w+');


            fputcsv($handle, array('Name', 'Given Name', 'Additional Name', 'Family Name', 'Yomi Name'
                , 'Given Name Yomi', 'Additional Name Yomi', 'Family Name Yomi'
                , 'Name Prefix', 'Name Suffix', 'Initials', 'Nickname', 'Short Name', 'Maiden Name'
                , 'Birthday', 'Gender', 'Location', 'Billing Information', 'Directory Server', 'Mileage Occupation'
                , 'Hobby', 'Sensitivity', 'Priority', 'Subject', 'Notes', 'Group Membership', 'E-mail 1 - Type', 'E-mail 1 - Value'
                , 'E-mail 2 - Type', 'E-mail 2 - Value', 'Phone 1 - Type', 'Phone 1 - Value'), ',');

            $repo = $this->getDoctrine()->getRepository('CodersBookBundle:Person');
            $repoGroup = $this->getDoctrine()->getRepository('CodersBookBundle:CLGroup');

            $group = $repoGroup->find($id);
            if (!$group) {
                return [
                    'error' => 'Nie ma takiej grupy'
                ];
            }
            $persons = $repo->findBy(['clGroup' => $group]);

            foreach ($persons as $person) {
                fputcsv(
                        $handle, array($person->getName(), '', '', '', ''
                    , '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''
                    , '', '', '', '', '', '* CodersLab ::: ' . $person->getClGroup(), '*', $person->getEmail()
                    , '', '', 'Mobile', $person->getPhone()), ','
                );
            }
            fclose($handle);
            flush();
        });
        
        $repoGroup2 = $this->getDoctrine()->getRepository('CodersBookBundle:CLGroup');
        $group2 = $repoGroup2->find($id);
        
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="CodersLab ' .$group2->getname().'.csv"');

        return $response->send();
    }

    /**
     * @Route("/download/{id}", name = "person_admin_download")
     * 
     */
    public function downloadPersonAction($id) {
        $repo = $this->getDoctrine()->getRepository('CodersBookBundle:Person');
        $person = $repo->find($id);
        $cvName = $person->getCvFN();
        $file = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/' . $cvName;

        $response = new BinaryFileResponse($file);
        $response->headers->set('Content-Type', 'application/octet-stream');

        $ext = pathinfo($file, PATHINFO_EXTENSION);

        $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, $person->getName() . '.' . $ext
        );
        return $response;
    }

    /**
     * @Route("/export_zip/{id}", name = "person_export_zip")
     * 
     */
    public function exportAction($id) {
        $repo = $this->getDoctrine()->getRepository('CodersBookBundle:Person');
        $repoGroup = $this->getDoctrine()->getRepository('CodersBookBundle:CLGroup');

        $group = $repoGroup->find($id);
        if (!$group) {
            return [
                'error' => 'Nie ma takiej grupy'
            ];
        }

        $persons = $repo->findBy(['clGroup' => $group]);

        $zip = new \ZipArchive();
        $zipName = $group->getName() . ".zip";
        $dir = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/' . $zipName;

        $zip->open($dir, \ZipArchive::CREATE);
        foreach ($persons as $person) {
            if ($person->getCvFN() == '') {
                continue;
            }
            $file = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/' . $person->getCvFN();
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $personCV = $person->getName() . '.' . $ext;
            $zip->addFromString($personCV, file_get_contents($file));
        }
        $zip->close();
        $response = new BinaryFileResponse($dir);
        $response->headers->set('Content-Type', 'application/octet-stream');


        $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, $zipName
        );
        $response->deleteFileAfterSend(true);
        return $response;
    }

}
