<div id="messageResult" class="label label-success hidden"></div>
<form role="form" id="addForm" target="upload_target" onsubmit="return startUpload()" action="{raURL node=$curnode.name}" method="post" enctype="multipart/form-data">
	<div class="form-group">
	  <label for="inputPerson"><span class="required">* </span>{"My name is / I represent a company"|t}:</label>
	  <input type="text" name="name" class="form-control" id="inputPerson">
	</div>
	<div class="form-group">
	  <label for="inputContact"><span class="required">* </span>{"Contact phone"|t} / E-mail:</label>
	  <input type="text" name="contact" class="form-control" id="inputContact">
	</div>  
	<div class="form-group">
	  <label for="inputMessage"><span class="required">* </span>{"Text ads"|t}:</label>
	  <textarea name="message" class="form-control" id="inputMessage"></textarea>
	</div>
	<div class="form-group">
	  <label for="inputFile">{"Upload foto"|t}</label>
	  <input name="foto" type="file" id="inputFile">
	  <p class="help-block">{"The maximum amount of photos 1MB."|t}</p>
	</div>
	<p class="required">* {"Fields are required"|t}</p>
</form>