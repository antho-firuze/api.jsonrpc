<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Core_charges extends CI_Controller 
{
    function __construct()
    {
      parent::__construct();
 
      if(!$this->input->is_cli_request())
      { 
        // echo 'Not allowed';
        // exit();
      }
    }
 
    
    
 }