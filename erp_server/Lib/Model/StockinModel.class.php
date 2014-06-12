<?php

/**
 * @filename StockinModel.class.php 
 * @encoding UTF-8 
 * @author nemo.xiaolan <a href="mailto:335454250@qq.com">335454250@qq.com</a>
 * @link <a href="http://www.sep-v.com">http://www.sep-v.com</a>
 * @license http://www.sep-v.com/code-license
 * @datetime 2013-11-13  14:18:25
 * @Description
 * 
 */
class StockinModel extends CommonModel {
    
    protected $_auto = array(
        array("status", 0),
        array("user_id", "getCurrentUid", 1, "function")
    );
    
    protected $workflowAlias = "stockin";
    
    /**
     * 创建新单据
     */
    public function newBill($billData, $billItems) {
        if(!$billItems) {
            return false;
        }
        $this->startTrans();
        
        if(!$billData["bill_id"]){
            $billData["bill_id"] = makeBillCode("RK");
        }
        
        $billId = $this->add($billData);
//        echo $this->getLastSql();exit;
//        echo $billId;exit;
        $itemsModel = D("StockinDetail");
        foreach($billItems as $billItem) {
            $billItem["stockin_id"] = $billId;
//            print_r($billItem);
            $id = $itemsModel->add($billItem);
            if(!$id) {
                $this->rollback();
                return false;
            }
        }
        
        $this->commit();
        
        //单据进入初始工作流程
        import("@.Workflow.Workflow");
        $workflow = new Workflow($this->workflowAlias);
        $node = $workflow->doNext($billId, "", true);
        return $billId;
        
    }
    
    public function editBill($bill, $rows) {
        $detailModel = D("StockinDetail");
        $this->startTrans();
        
        unset($bill["dateline"]);
        unset($bill["status"]);
        unset($bill["bill_id"]);
        unset($bill["user_id"]);
        unset($bill["source_model"]);
        unset($bill["source_id"]);
        
        if(false === $this->save($bill)) {
//            echo $this->getLastSql();
            $this->rollback();
            return false;
        }
//        echo $this->getLastSql();exit;
        
        foreach($rows as $row) {
            if(!$row["id"]) {
                continue;
            }
            if(false === $detailModel->save($row)) {
//                echo $detailModel->getLastSql();exit;
                $this->rollback();
                return false;
            }
            echo $detailModel->getLastSql();
        }
        
        $this->commit();
        return true;
    }
    
    /**
     * 格式化POST或者PUT的数据
     */
    public function formatData($postData) {
        $stockinModel = $this;
        
        $billData = array(
            "bill_id" => makeBillCode(C("BILL_PREFIX.Stockin")),
            "subject" => $postData["subject"],
            "dateline"=> strtotime($postData["inputTime"]),
            "status"  => 0,
            "user_id" => getCurrentUid(),
            "stock_manager" => 0,
            "total_num"=> $postData["total_num"],
            "memo"    => $postData["memo"],
            "type_id" => $postData["type_id"]
        );
        
        $id = abs(intval($_GET["id"]));
        if($id) {
            $billData["id"] = $id;
        }
        
        $data = $postData["rows"];
        $billItems = array();
        foreach($data as $k=> $billItem) {
            if(!$billItem) {
                continue;
            }
            list($factory_code, $goodsId, $catid) = explode("_", $billItem["goods_id"]);
            $billItems[$k] = array(
                "goods_id"   => $goodsId,
                "num"        => $billItem["num"],
                "factory_code_all" => makeFactoryCode($billItem, $factory_code),
                "memo" => $billItem["memo"],
                "stock_id"   => $billItem["stock"]//$billItem["stock_id"]
            );
            if($billItem["id"]) {
                $billItems[$k]["id"] = $billItem["id"];
            }
        }
        
        return array(
            $billData,
            reIndex($billItems)
        );
    }
    
    
}
