<?php
clog2ini("templates.correos");
clog1seq(1);
?>
          <div id="area_central_correos" class="central">
            <h1>Correos</h1>
            <form method="post" name="formaCorreo" id="formaCorreo" class="noApply" enctype="multipart/form-data">
                <input type="hidden" name="correo_admin" id="correo_admin" value="1">
            </form>
          </div>
<?php
clog1seq(-1);
clog2end("templates.catalogo");
