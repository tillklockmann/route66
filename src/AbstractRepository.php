<?php
namespace Route66;

abstract class AbstractRepository 
{

    protected $db;

    public function __construct($db = null)
    {
        $this->db = $db;
    }
    
}
