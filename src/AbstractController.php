<?php
namespace Route66;
use Route66\View;
use Route66\AbstractRepository;

abstract class AbstractController 
{
    /** @var AbstractRepository */
    protected $repo;

    /** @var View */
    protected $view;

    public function __construct(AbstractRepository $repo, View $view)
    {
        $this->repo = $repo;
        $this->view = $view;
    }
    
}