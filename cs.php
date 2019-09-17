<!DOCTYPE>  
<html>  
<head>  
<title> formdata file jquery ajax upload</title>  
</head>  
  
<body>  
<div id="uploadForm">
    <input id="file" type="file"/>
    <button id="upload" type="button">upload</button>
</div>
<script>
var formData = new FormData();
formData.append('file', $('#file')[0].files[0]);
$.ajax({
    url: '/upload',
    type: 'POST',
    cache: false,
    data: formData,
    processData: false,
    contentType: false
}).done(function(res) {
}).fail(function(res) {});
</script>
</body>  
</html>  