<?php
namespace QuoteDB\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Quote
 *
 * @ORM\Table()
 * @ORM\Entity
 */
 
class Quote
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="text", length=65535, nullable=false)
     */
    private $quote;

    /**
     * @var Author
     *
     * @ORM\ManyToOne(targetEntity="Author", cascade={"all"})
     */
    private $author;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", cascade={"all"})
     */
    private $insertedBy;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $approved;

    public function getId()
    {
        return $this->id;
    }

    public function getQuote()
    {
        return $this->quote;
    }

    public function setQuote($quote)
    {
        $this->quote = $quote;
        return $this;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
        return $this;
    }

    public function getInsertedBy()
    {
        return $this->insertedBy;
    }

    public function setInsertedBy($insertedBy)
    {
        $this->insertedBy = $insertedBy;
        return $this;
    }

    public function getApproved()
    {
        return $this->approved;
    }

    public function setApproved($approved)
    {
        $this->approved = $approved;
        return $this;
    }
 
}
