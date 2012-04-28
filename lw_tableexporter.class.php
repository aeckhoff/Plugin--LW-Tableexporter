<?php

/**************************************************************************
*  Copyright notice
*
*  Copyright 2011-2012 Logic Works GmbH
*
*  Licensed under the Apache License, Version 2.0 (the "License");
*  you may not use this file except in compliance with the License.
*  You may obtain a copy of the License at
*
*  http://www.apache.org/licenses/LICENSE-2.0
*  
*  Unless required by applicable law or agreed to in writing, software
*  distributed under the License is distributed on an "AS IS" BASIS,
*  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*  See the License for the specific language governing permissions and
*  limitations under the License.
*  
***************************************************************************/

class lw_tableexporter extends lw_plugin 
{
    function __construct() 
    {
        parent::__construct();
        $auth = lw_registry::getInstance()->getEntry("auth");
        if (!$auth->isGodmode()) {
            die("not allowed");
        }
        $this->transporter = new lw_db_transporter();
    }
    
    function buildPageOutput() 
    {
        $tables = $this->request->getRaw("tables");
        if ($this->request->getInt("export") == 1 && count($tables)>0) {
            if ($this->request->getAlnum("type") == 'data') {
                $data = $this->transporter->exportData($tables);
                $filename = date('YmdHis')."_exportData.xml";
            }
            else {
                $data = $this->transporter->exportTables($tables);
                $filename = date('YmdHis')."_exportStructure.xml";
            }
            if ($this->request->getInt("download") == 1) {
                $mimeType = lw_io::getMimeType("xml");
                if (strlen($mimeType)<1) {
                    $mimeType = "application/octet-stream";
                }            
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Content-Type: ".$mimeType);
                header("Content-disposition: attachment; filename=\"".$filename."\"");
                die($data);
                exit();
            }
            else {
                $out = '<textarea cols="70" rows="30">'.htmlspecialchars($data).'</textarea>';
                $out.= '<br/><br/><a href="'.lw_page::getInstance()->getUrl().'">zur&uuml;ck</a>';
            }
        }
        else {
            $out = $this->showTableList();
        }
        die($out);
    }
    
    function showTableList() 
    {
        $tables = $this->transporter->getAllTables();
        $out.= "<h1>Table Exporter</h1>";
        $out.= '<form action="'.lw_page::getInstance()->getUrl(array("export"=>"1")).'" method="post">';
        $out.= '    Download <input type="checkbox" name="download" value="1" /><br/>'.PHP_EOL;
        $out.= '    <select name="type">'.PHP_EOL;
        $out.= '    <option value="structure">Struktur</option>'.PHP_EOL;
        $out.= '    <option value="data">Daten</option>'.PHP_EOL;
        $out.= '    </select>'.PHP_EOL;
        $out.= '    <input type="submit" value="exportieren">';
        $out.= "    <table>";
        $prefix = strtoupper(trim($this->db->getPrefix()));
        $pl = strlen($prefix);
        foreach($tables as $table) {
            if (substr($table, 0, $pl) == $prefix || $this->request->getInt("showall") == 1) {
                $out.='            <tr><td><input type="checkbox" name="tables['.$table.']" value="1"></td><td>'.$table.'</td></tr>'.PHP_EOL;
            }
        }
        $out.= "    </table>";
        $out.= '    <input type="submit" value="exportieren">';
        $out.= "</form>";
        die($out);
    }
}
