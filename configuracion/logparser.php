<?php
if(!$hasUser) {
    header("Location: /".$_project_name."/");
    die("Redirecting to /".$_project_name."/");
}

clog2ini("configuracion.logparser");
clog1seq(1);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - M E T H O D S - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - //

clog1seq(-1);
clog2end("configuracion.logparser");
