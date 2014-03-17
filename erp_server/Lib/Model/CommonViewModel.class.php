<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CommonViewModel
 *
 * @author 志鹏
 */
class CommonViewModel extends ViewModel{
    
//    protected $status_lang = array(
//        "ineffective","effective"
//    );
//    
//    protected $status_class = array(
//        "normal","success"
//    );
    
    protected $workflowAlias;
    
    protected $workflowMainRowField = "id";
    
    public function getIndexArray($data, $value="name", $key="id") {
        if(!$data) {
            return array();
        }
        foreach($data as $k=>$v) {
            $return[$v[$key]] = $v[$value];
        }
        return $return;
    }
    
    /**
     * @override
     */
    public function select($options = array()) {
        $data = parent::select($options);
        if(!$data) {
            return $data;
        }
        foreach($data as $k=>$v) {
            if($v["dateline"]) {
                $data[$k]["dateline_lang"] = date("Y-m-d H:i:s", $v["dateline"]);
            }
            if(isset($v["status"])) {
                if(isset($this->status_lang)) {
                    $data[$k]["status_lang"] = L($this->status_lang[$v["status"]]);
                }
                if($this->status_class) {
                    $data[$k]["status_class"] = $this->status_class[$v["status"]];
                }
            }
            if(isset($v["factory_code"]) and $v["color_id"] and $v["standard_id"]) {
                $data[$k]["factory_code_all"] = sprintf("%s-%s-%s", $v["factory_code"],$v["color_id"],$v["standard_id"]);
            }
            
            $ids[] = $v[$this->workflowMainRowField];
            
        }
//        echo $this->workflowMainRowField;exit;
//        print_r($ids);exit;
        /**
         * 工作流
         */
        if($this->workflowAlias) {
            import("@.Workflow.Workflow");
            $workflow = new Workflow($this->workflowAlias);
            $processData = $workflow->getListProcess($ids);
            foreach($data as $k=> $v) {
                $data[$k]["processes"] = $processData[$v[$this->workflowMainRowField]];
            }
        }
        return $data;
    }
    
    /**
     * @override
     */
    public function find($options = array()) {
        $data = parent::find($options);
        if(!$data) {
            return $data;
        }
        if($data["dateline"]) {
            $data["dateline_lang"] = date("Y-m-d H:i:s", $data["dateline"]);
        }
        if(isset($data["status"])) {
            if(isset($this->status_lang)) {
                $data["status_lang"] = L($this->status_lang[$data["status"]]);
            }
            if($this->status_class) {
                $data["status_class"] = $this->status_class[$data["status"]];
            }
        }
        if(isset($data["factory_code"]) and $data["color_id"] and $data["standard_id"]) {
            $data["factory_code_all"] = sprintf("%s-%s-%s", $data["factory_code"],$data["color_id"],$data["standard_id"]);
        }
        
        /**
         * 工作流
         */
        if($this->workflowAlias) {
            import("@.Workflow.Workflow");
            $workflow = new Workflow($this->workflowAlias);
            $processData = $workflow->getCurrentProcess($data[$this->workflowMainRowField]);
            $data["processes"] = $processData;
        }
        
        return $data;
    }
    
}

?>