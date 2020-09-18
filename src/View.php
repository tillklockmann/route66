<?php
namespace Route66;

use Exception;

class View
{
    protected $template;
    protected $data = [];
    protected $pathToViewsFolder;

    public function __construct(string $pathToViewsFolder){
        $this->pathToViewsFolder = $pathToViewsFolder;
    }

    /**
     * @param string $template 
     * @return View 
     * @throws Exception 
     */
    public function template(string $template = 'template-not-found') : self
    {   
        $template = $this->pathToViewsFolder  . DIRECTORY_SEPARATOR . $template . '.view.php';
        if (is_file($template)) {
            $this->template = $template;
        } else {
            throw new \Exception("File not found", 1);
        }
        return $this;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function render()
    {
        extract($this->data);
        
        ob_start();
        include($this->template);
        echo ob_get_clean();
    }
}

 ?>
