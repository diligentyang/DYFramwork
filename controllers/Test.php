<?php

defined("ACCESS") or define("ACCESS", true);

class Test extends DYController
{
    function actionIndex()
    {
        $this->model("TestModel")->testModelMethod();
    }

    function actionArticle()
    {
        $model=$this->model("Admin/AdminModel");
        $model->test();
        $res=$this->db()->query("select * from user");
        var_dump($res);
        //如果能查到东西，就返回查询结果数组，否则返回false
        $res1 = $this->db()->bindquery("select * from user where username=? and password=?",array("admin","123456"));
        var_dump($res1);
    }
}