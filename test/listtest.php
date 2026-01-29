<?php
echo "INICIO\n";
$arr=["id"=>"51","codigo"=>"A-085","rfc"=>"ASY130801IK3","razonSocial"=>"AVAXIS SYSTEMS","cuenta"=>"210500004"];
testCallByRef($arr);
// VERSION PHP REQUERIDA: 7.1
// VERSION PHP INSTALADA: 7.0.11
list("id" => $dbPrvId, "codigo" => $dbCodProv, "rfc" => $dbPrvRFC, "razonSocial" => $dbRazSoc, "cuenta" => $dbCuenta) = $arr;

echo "ID=$dbPrvId, CODIGO=$dbCodProv, RFC=$dbPrvRFC, RazSoc=$dbRazSoc, CTA=$dbCuenta\n";
function testCallByRef($arr) {
    $arr["codigo"]="X-999";
}
