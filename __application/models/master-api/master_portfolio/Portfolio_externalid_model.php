<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Portfolio_externalid_model extends CI_Model
{
	function __construct(){
		parent::__construct();
		$this->load->library('System');
		$this->load->library('Portfolio');
	}

	private function _sql($request)
	{
		$subQuery =  'Select PortfolioID From system_access_portfolio 
					Where UserID = '.$request->user_id.' And simpiID = '.$request->simpi_id;

		if (isset($request->params->fields) && !empty($request->params->fields)) 
		   $this->db->select($request->params->fields);
		else
			$this->db->select('T1.simpiID, T1.PortfolioID, T1.PortfolioCode, T2.SystemID, T3.SystemCode, T3.SystemName, T2.PortfolioExternalCode');  
		$this->db->from('master_portfolio T1');
		$this->db->join('master_portfolio_id_external T2', 'T1.simpiID = T2.simpiID And T1.PortfolioID = T2.PortfolioID');  
		$this->db->join('parameter_securities_externalsystem T3', 'T2.SystemID = T3.SystemID');  
		$this->db->where('T1.simpiID', $request->simpi_id);

		if ($request->log_access == 'license') {
			return [TRUE, NULL]; //full access
		} elseif ($request->log_access == 'session') {
			$this->db->where("'T1.PortfolioID' IN ($subQuery)", NULL, FALSE); //user assignment
			return [TRUE, NULL];
		} elseif ($request->log_access == 'token') {
			//can not akses: must via apps
			$return = $this->system->error_data('00-1', $request->LanguageID, 'access right');
			return [FALSE, ['message' => $return['message'], 'error' => $return['error']]];		 
		} elseif ($request->log_access == 'apps') {
			//can not akses: must via apps
			$return = $this->system->error_data('00-1', $request->LanguageID, 'access right');
			return [FALSE, ['message' => $return['message'], 'error' => $return['error']]];		 
		} else {
			$return = $this->system->error_data('00-1', $request->LanguageID, 'access right');
			return [FALSE, ['message' => $return['message'], 'error' => $return['error']]];		 
		}	
	}

	function load($request)
	{
		list($success, $return) = $this->system->is_valid_access2($request);
		if (!$success) return [FALSE, $return];

		if (!isset($request->params->SystemID) || empty($request->params->SystemID)) {
			$return = $this->system->error_data('00-1', $request->LanguageID, 'parameter SystemID');
			return [FALSE, ['message' => $return['message'], 'error' => $return['error']]];		 
		}

		list($success, $return) =  $this->system->database_server($request, 'master');
		if (!$success) return [FALSE, $return];
		$this->load->database($return);
		list($success, $return) = $this->_sql($request);
		if (!$success) return [FALSE, $return];
		$this->db->where('T2.SystemID', $request->params->SystemID);
		if (isset($request->params->PortfolioID) && !empty($request->params->PortfolioID)) {
			$this->db->where('T1.PortfolioID', $request->params->PortfolioID);
		} elseif (isset($request->params->PortfolioCode) && !empty($request->params->PortfolioCode)) {
			$this->db->where('T1.PortfolioCode', $request->params->PortfolioCode);
		} else {
			$return = $this->system->error_data('00-1', $request->LanguageID, 'parameter portfolio');
			return [FALSE, ['message' => $return['message'], 'error' => $return['error']]];		 
		}
		$data = $this->f->get_result_paging($request);

		$request->log_type	= 'data';	
		$this->system->save_billing($request);
		
		return $data;		
	}	

	function search($request)
	{	
		list($success, $return) = $this->system->is_valid_access2($request);
		if (!$success) return [FALSE, $return];

		list($success, $return) =  $this->system->database_server($request, 'master');
		if (!$success) return [FALSE, $return];
		$this->load->database($return);
		list($success, $return) = $this->_sql($request);
		if (!$success) return [FALSE, $return];
		if (isset($request->params->CcyID) && !empty($request->params->CcyID)) 
			$this->db->where('T1.CcyID', $request->params->CcyID);
		if (isset($request->params->SystemID) && !empty($request->params->SystemID)) 
			$this->db->where('T2.SystemID', $request->params->SystemID);
		if (isset($request->params->PortfolioID) && !empty($request->params->PortfolioID)) {
			if (is_array($request->params->PortfolioID)) 
				$this->db->where_in('T1.PortfolioID', $request->params->PortfolioID);
			else 
				$this->db->where('T1.PortfolioID', $request->params->PortfolioID);
		} elseif (isset($request->params->PortfolioCode) && !empty($request->params->PortfolioCode)) {
			if (is_array($request->params->PortfolioCode)) 
				$this->db->where_in('T1.PortfolioCode', $request->params->PortfolioCode);
			else 
				$this->db->where('T1.PortfolioCode', $request->params->PortfolioCode);
		} else {
			if (isset($request->params->portfolio_keyword)) {
				$data = $this->security->xss_clean($request->params->portfolio_keyword);
				$strKeyword = "(T1.PortfolioCode LIKE '%".$data."%'"
							 ." or T1.PortfolioNameFull LIKE '%".$data."%'"
							 ." or T1.PortfolioNameShort LIKE '%".$data."%')";
				$this->db->where($strKeyword);
			}
			if (isset($request->params->TypeID) && !empty($request->params->TypeID)) $this->db->where('T1.TypeID', $request->params->TypeID);
			if (isset($request->params->AccountID) && !empty($request->params->AccountID)) $this->db->where('T1.AccountID', $request->params->AccountID);
			if (isset($request->params->AssetTypeID) && !empty($request->params->AssetTypeID)) $this->db->where('T1.AssetTypeID', $request->params->AssetTypeID);
			if (isset($request->params->StatusID) && !empty($request->params->StatusID)) $this->db->where('T1.StatusID', $request->params->StatusID);
			if (isset($request->params->CcyID) && !empty($request->params->CcyID)) $this->db->where('T1.CcyID', $request->params->CcyID);
		}			
		$data = $this->f->get_result_paging($request);

		$request->log_type	= 'data';	
		$this->system->save_billing($request);
		
		return $data;			 
	}

	function external_get($request)
	{
		list($success, $return) = $this->system->is_valid_access2($request);
		if (!$success) return [FALSE, $return];
 
		if (!isset($request->params->SystemID) || empty($request->params->SystemID)) {
			$return = $this->system->error_data('00-1', $request->LanguageID, 'parameter SystemID');
			return [FALSE, ['message' => $return['message'], 'error' => $return['error']]];		 
		}

		list($success, $return) =  $this->system->database_server($request, 'master');
		if (!$success) return [FALSE, $return];
		$this->load->database($return);
		list($success, $return) = $this->_sql($request);
		if (!$success) return [FALSE, $return];
		$this->db->where('T1.SystemID', $request->params->SystemID);
		if (isset($request->params->PortfolioID) && !empty($request->params->PortfolioID)) {
			$this->db->where('T2.PortfolioID', $request->params->PortfolioID);
		} elseif (isset($request->params->PortfolioCode) && !empty($request->params->PortfolioCode)) {
			$this->db->where('T2.PortfolioCode', $request->params->PortfolioCode);
		} else {
			$return = $this->system->error_data('00-1', $request->LanguageID, 'parameter portfolio');
			return [FALSE, ['message' => $return['message'], 'error' => $return['error']]];		 
		}
		$row = $this->db->get()->row();
        if (!$row) {
			$return = $this->system->error_data('00-2', $request->LanguageID, 'portfolio external identification');
			return [FALSE, ['message' => $return['message'], 'error' => $return['error']]];		 
        }

		$request->log_size = mb_strlen(serialize($row), '8bit');
		$request->log_type	= 'data';	
		$this->system->save_billing($request);

		return [TRUE, ['result' => ['PortfolioExternalCode' => $row->PortfolioExternalCode]]];
	}

	function external_code($request)
	{
		list($success, $return) = $this->system->is_valid_access2($request);
		if (!$success) return [FALSE, $return];

		if (!isset($request->params->PortfolioExternalCode) || empty($request->params->PortfolioExternalCode)) {
			$return = $this->system->error_data('00-1', $request->LanguageID, 'parameter PortfolioExternalCode');
			return [FALSE, ['message' => $return['message'], 'error' => $return['error']]];		 
		}
		if (!isset($request->params->SystemID) || empty($request->params->SystemID)) {
			$return = $this->system->error_data('00-1', $request->LanguageID, 'parameter SystemID');
			return [FALSE, ['message' => $return['message'], 'error' => $return['error']]];		 
		}

		list($success, $return) =  $this->system->database_server($request, 'master');
		if (!$success) return [FALSE, $return];
		$this->load->database($return);
		list($success, $return) = $this->_sql($request);
		if (!$success) return [FALSE, $return];
		$this->db->where('T1.SystemID', $request->params->SystemID);
		$this->db->where('T1.PortfolioExternalCode', $request->params->PortfolioExternalCode);
		$row = $this->db->get()->row();
        if (!$row) {
			$return = $this->system->error_data('00-2', $request->LanguageID, $request->params->PortfolioExternalCode);
			return [FALSE, ['message' => $return['message'], 'error' => $return['error']]];		 
        }

		$request->log_size = mb_strlen(serialize($row), '8bit');
		$request->log_type	= 'data';	
		$this->system->save_billing($request);

		return [TRUE, ['result' => ['PortfolioCode' => $row->PortfolioCode]]];
	}

	function external_id($request)
	{
		list($success, $return) = $this->system->is_valid_access2($request);
		if (!$success) return [FALSE, $return];

		if (!isset($request->params->PortfolioExternalCode) || empty($request->params->PortfolioExternalCode)) {
			$return = $this->system->error_data('00-1', $request->LanguageID, 'parameter PortfolioExternalCode');
			return [FALSE, ['message' => $return['message'], 'error' => $return['error']]];		 
		}
		if (!isset($request->params->SystemID) || empty($request->params->SystemID)) {
			$return = $this->system->error_data('00-1', $request->LanguageID, 'parameter SystemID');
			return [FALSE, ['message' => $return['message'], 'error' => $return['error']]];		 
		}

		list($success, $return) =  $this->system->database_server($request, 'master');
		if (!$success) return [FALSE, $return];
		$this->load->database($return);
		list($success, $return) = $this->_sql($request);
		if (!$success) return [FALSE, $return];
		$this->db->where('T1.SystemID', $request->params->SystemID);
		$this->db->where('T1.PortfolioExternalCode', $request->params->PortfolioExternalCode);
		$row = $this->db->get()->row();
        if (!$row) {
			$return = $this->system->error_data('00-2', $request->LanguageID, $request->params->PortfolioExternalCode);
			return [FALSE, ['message' => $return['message'], 'error' => $return['error']]];		 
        }

		$request->log_size = mb_strlen(serialize($row), '8bit');
		$request->log_type	= 'data';	
		$this->system->save_billing($request);

		return [TRUE, ['result' => ['PortfolioID' => $row->PortfolioID]]];
	}

}