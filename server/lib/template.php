<?php


namespace Lib;
class Template {
    protected $templateContent='';
    protected $templatePath='';
    protected $templateName='';
    protected $leftDelimiter='{';
    protected $rightDelimiter='}';
    
    public function __construct($templatePath,$templateName) {
        $this->templatePath=$templatePath;
        $this->templateName=$templateName;
        
        $this->load();
        $this->parse();
    }
    
    public function display() {
        echo $this->templateContent;
    }
    
    protected function replace($matches) {        
        $match = str_replace('{', '', str_replace('}', '', str_replace('@', '', $matches[0])));        
        return \Server::get($match);
    }
    protected function parse() {        
        while( preg_match('/{([@a-zA-Z\_\-])*?}/i', $this->templateContent) )
        {   
            $callable = ['\Lib\Template','replace'];
            $this->templateContent = preg_replace_callback(
                    '/{([@a-zA-Z\_\-])*?}/i',
                    $callable,
                    $this->templateContent
            );            
        }
        
    }
    
    protected function load() {
        $filepath = $this->templatePath.'/'.$this->templateName.'.html';
        $this->templateContent = file_get_contents($filepath);
    }
}
