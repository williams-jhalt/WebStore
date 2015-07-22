<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Weborder
 *
 * @ORM\Table(name="weborder_audit")
 * @ORM\Entity(repositoryClass="WeborderAuditRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class WeborderAudit {

    /**
     * @ORM\ManyToOne(targetEntity="Weborder", inversedBy="audits")
     * @ORM\JoinColumn(name="weborder_id", referencedColumnName="id")
     * */
    private $weborder;

    /**
     * @var string
     * 
     * @ORM\Column(name="order_number", type="string", length=255)
     * @ORM\Id
     */
    private $orderNumber;

    /**
     * @var string
     * 
     * @ORM\Column(name="record_date", type="string", length=255)
     * @ORM\Id
     */
    private $recordDate;

    /**
     * @var string
     * 
     * @ORM\Column(name="record_time", type="string", length=255)
     * @ORM\Id
     */
    private $recordTime;

    /**
     * @var string
     * 
     * @ORM\Column(name="record_type", type="string", length=255)
     */
    private $recordType;

    /**
     * @var string
     * 
     * @ORM\Column(name="status_code", type="string", length=255)
     */
    private $statusCode;

    /**
     * @var string
     * 
     * @ORM\Column(name="comment", type="string", length=255)
     */
    private $comment;

    public function getId() {
        return $this->id;
    }

    public function getOrderNumber() {
        return $this->orderNumber;
    }

    public function getTimestamp() {
        $timeStr = str_pad($this->recordTime, 6, "0", STR_PAD_LEFT);
        return DateTime::createFromFormat("Y-m-d His", "{$this->recordDate} {$timeStr}");
    }

    public function getRecordType() {
        return $this->recordType;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }

    public function getComment() {
        return $this->comment;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setOrderNumber($orderNumber) {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function setRecordType($recordType) {
        $this->recordType = $recordType;
        return $this;
    }

    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function setComment($comment) {
        $this->comment = $comment;
        return $this;
    }

    public function getWeborder() {
        return $this->weborder;
    }

    public function setWeborder($weborder) {
        $this->weborder = $weborder;
        return $this;
    }

    public function getRecordDate() {
        return $this->recordDate;
    }

    public function getRecordTime() {
        return $this->recordTime;
    }

    public function setRecordDate($recordDate) {
        $this->recordDate = $recordDate;
        return $this;
    }

    public function setRecordTime($recordTime) {
        $this->recordTime = $recordTime;
        return $this;
    }

}
