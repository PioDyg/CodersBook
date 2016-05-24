<?php

namespace CodersLab\CodersBookBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Person
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="CodersLab\CodersBookBundle\Entity\PersonRepository")
 */
class Person {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=15)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="github", type="string", length=255)
     */
    private $github;

    /**
     * @var string
     *
     * @ORM\Column(name="linkedin", type="string", length=255)
     */
    private $linkedin;

    /**
     *
     * @var type 
     * @ORM\ManyToOne(targetEntity="CLGroup", inversedBy="persons")
     * @ORM\JoinColumn(name="clgroup_id", referencedColumnName="id")
     */
    private $clGroup;
    
    /**
     * @var string
     *
     * @ORM\Column(name="imageFN", type="string", length=100)
     */
    private $imageFN;
    
    /**
     * @var string
     *
     * @ORM\Column(name="cvFN", type="string", length=100)
     */
    private $cvFN;
   
    public function __construct() {
        $imageFN = '';
        $cvFN = '';
    }
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Person
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Person
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Person
     */
    public function setPhone($phone) {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone() {
        return $this->phone;
    }

    /**
     * Set github
     *
     * @param string $github
     * @return Person
     */
    public function setGithub($github) {
        $this->github = $github;

        return $this;
    }

    /**
     * Get github
     *
     * @return string 
     */
    public function getGithub() {
        return $this->github;
    }

    /**
     * Set linkedin
     *
     * @param string $linkedin
     * @return Person
     */
    public function setLinkedin($linkedin) {
        $this->linkedin = $linkedin;

        return $this;
    }

    /**
     * Get linkedin
     *
     * @return string 
     */
    public function getLinkedin() {
        return $this->linkedin;
    }


    /**
     * Set clGroup
     *
     * @param \CodersLab\CodersBookBundle\Entity\CLGroup $clGroup
     * @return Person
     */
    public function setClGroup(\CodersLab\CodersBookBundle\Entity\CLGroup $clGroup = null)
    {
        $this->clGroup = $clGroup;

        return $this;
    }

    /**
     * Get clGroup
     *
     * @return \CodersLab\CodersBookBundle\Entity\CLGroup 
     */
    public function getClGroup()
    {
        return $this->clGroup;
    }

    /**
     * Set imageFN
     *
     * @param string $imageFN
     * @return Person
     */
    public function setImageFN($imageFN)
    {
        $this->imageFN = $imageFN;

        return $this;
    }

    /**
     * Get imageFN
     *
     * @return string 
     */
    public function getImageFN()
    {
        return $this->imageFN;
    }

    /**
     * Set cvFN
     *
     * @param string $cvFN
     * @return Person
     */
    public function setCvFN($cvFN)
    {
        $this->cvFN = $cvFN;

        return $this;
    }

    /**
     * Get cvFN
     *
     * @return string 
     */
    public function getCvFN()
    {
        return $this->cvFN;
    }
}
