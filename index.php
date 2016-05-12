<?php

function is_json($string) {
 $json = json_decode($string);
 return (json_last_error() == JSON_ERROR_NONE)? $json : false;
}

if(isset($_POST['save'])){
  $author = @$_POST['author']? htmlentities( $_POST['author'] ) : 'Anônimo';
  $json = is_json( $_POST['save'] );
  $file = basename($_SERVER['HTTP_REFERER']);
  $data = [];

  if($file && $json) {

    $data['author'] = $author;
    $data['data'] = $json;
    $data['time'] = time();

    $file = "data/$file.json";

    if(file_exists($file)) {
      $json = json_decode(file_get_contents($file));
      $json[] = $data;
    } else {
      $json = [];
      $json[] = $data;
    }
    // print_r($data);
    file_put_contents($file, json_encode($json));
    echo 'Mapa salvo!';
  }
  die;
} ?>
<!DOCTYPE html>
<html>
<head>
  <title>Mind Map</title>
  <meta name="description" content="A mind map editor, showing how subtrees can be moved, copied, deleted, and laid out." />
  <!-- Copyright 1998-2016 by Northwoods Software Corporation. -->
  <meta charset="UTF-8">
  <script src="go2.js"></script>
  <link href="helpers.min.css" rel="stylesheet" type="text/css" />  <!-- you don't need to use this -->
  <script src="piratemap.js"></script>
  <style>
  html, body { height: 100%; width: 100%; font-family:Helvetica, Arial; }
  * { box-sizing: border-box }
  #history a { color:#333; font-size:.7em }
  #history a:hover { background: #ccc}
  </style>
  <script>
  function save_json(data){
    save();
    var formData = new FormData();
        formData.append('save', document.getElementById('mySavedModel').value);
        formData.append('author', document.getElementById('author').value);
    var xmlHttp = new XMLHttpRequest();
        xmlHttp.onreadystatechange = function()
        {
            if(xmlHttp.readyState == 4 && xmlHttp.status == 200)
            {
                alert(xmlHttp.responseText);
            }
        }
    xmlHttp.open("post", "");
    xmlHttp.send(formData);
  }
  function loadHistory(a){
    var data = decodeHTMLEntities(a.getAttribute('data-json'));
    document.getElementById('mySavedModel').value = data;
    load();
    layoutAll();
    return false;
  }
  function decodeHTMLEntities(text) {
    var entities = [
        ['apos', '\''],
        ['amp', '&'],
        ['lt', '<'],
        ['gt', '>']
    ];

    for (var i = 0, max = entities.length; i < max; ++i)
        text = text.replace(new RegExp('&'+entities[i][0]+';', 'g'), entities[i][1]);

    return text;
}
  </script>
</head>
<body onload="init()" layout="no-margin table">

<div id="myDiagramDiv" style="height:100%;"></div>

<div text="top" layout="table-cell gray-10" style="width:200px; border-left:2px solid #ccc">
  <menu text="center" layout="gray-20 no-margin padding-10">
    <button onclick="save_json()">Salvar</button>
    <button onclick="layoutAll()">Reorganizar</button>
  </menu>
  <input type="text" id="author" placeholder="Digite seu nome" layout="fluid padding-5" />
  <div layout="padding-5 v-margin-10">
    <small>json:</small>
    <textarea id="mySavedModel" layout="fluid gray-10" rows="3" style="font-size:.7em">
      <?php
      $file = basename($_SERVER['REQUEST_URI']);
      if($file && file_exists("data/$file.json")) {
        $data = json_decode(file_get_contents("data/$file.json"));
        echo json_encode(end($data)->data);
      }
      else
        echo file_get_contents("default.json");
      ?>
     </textarea>
     <div text="center">
       <button id="SaveButton" onclick="save()">Atualizar</button>
       <button onclick="load()">Carregar</button>
     </div>
    <div id="history" <?php if(!$data) echo 'style="display:none"' ?>>
      <hr />
      <small>Histórico:</small> <br />
      <?php
      foreach (array_reverse($data) as $item) {
        $date = date('d/m/Y i:m', $item->time);
        $json = htmlentities(json_encode($item->data));
        echo "<a href='#' onclick='return loadHistory(this)' layout='block padding-5' data-json=\"$json\">$date por $item->author</a>";
      }
      ?>
    </div>

  </div>

</div>

</body>
</html>
