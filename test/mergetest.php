<?php
$array1=["1","11","6","18","3","12","7"];
$array2=["7","3","4","10","11"];
$arrayM=array_merge($array1,$array2);
$arrayU=array_unique($arrayM);
$arrayS=$arrayU;
sort($arrayS);

echo "ARRAY1 = [ '".implode("', '", $array1)."' ]\n";
echo "ARRAY2 = [ '".implode("', '", $array2)."' ]\n";
echo "ARRAYM = [ '".implode("', '", $arrayM)."' ]\n";
echo "ARRAYU = [ '".implode("', '", $arrayU)."' ]\n";
echo "ARRAYS = [ '".implode("', '", $arrayS)."' ]\n";

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
//require_once dirname(__DIR__)."/bootstrap.php";
/*
class TEST {
    const TABLA_FECHA=["eName"=>"TABLE","className"=>"pad2c other","eChilds"=>[["eName"=>"TBODY","eChilds"=>[["eName"=>"TR","eChilds"=>[["eName"=>"TH","eText"=>"Fecha Inicial:"],["eName"=>"TD","eChilds"=>[["eName"=>"INPUT","type"=>"text","id"=>"fechaIniFld"]]],["eName"=>"TD","rowSpan"=>"2","eChilds"=>[["eName"=>"IMG","src"=>"imagenes/icons/add.png","width"=>"45","height"=>"45"]]]]],["eName"=>"TR","eChilds"=>[["eName"=>"TH","eText"=>"Fecha Final:"],["eName"=>"TD","eChilds"=>[["eName"=>"INPUT","type"=>"text","id"=>"fechaFinFld"]]]]]]]]];
    public static listaFiltros=["uno"=>["titulo"=>"PRIMERO","contenido"=>[array_merge(self::TABLA_FECHA,[])]]];
}
*/
/*
const TABLA_FECHA=["eName"=>"TABLE","className"=>"pad2c other","eChilds"=>[["eName"=>"TBODY","eChilds"=>[["eName"=>"TR","eChilds"=>[["eName"=>"TH","eText"=>"Fecha Inicial:"],["eName"=>"TD","eChilds"=>[["eName"=>"INPUT","type"=>"text","id"=>"fechaIniFld"]]],["eName"=>"TD","rowSpan"=>"2","eChilds"=>[["eName"=>"IMG","src"=>"imagenes/icons/add.png","width"=>"45","height"=>"45"]]]]],["eName"=>"TR","eChilds"=>[["eName"=>"TH","eText"=>"Fecha Final:"],["eName"=>"TD","eChilds"=>[["eName"=>"INPUT","type"=>"text","id"=>"fechaFinFld"]]]]]]]]];
const TABLA_LISTA=["eName"=>"TABLE","className"=>"pad2c","eChilds"=>[["eName"=>"TBODY","eChilds"=>[["eName"=>"TR","eChilds"=>[["eName"=>"TH","className"=>"nowrap","eText"=>"Lista:"],["eName"=>"TD","eChilds"=>[["eName"=>"SELECT","id"=>"listFld","eChilds"=>[["eName"=>"OPTION","eText"=>"Todas"]]]]],["eName"=>"TD","eChilds"=>[["eName"=>"IMG","src"=>"imagenes/icons/add.png","id"=>"appendFilterButton","className"=>"hoverDarkF5 pointer","width"=>"21.5","height"=>"21.5"]]]]]]]]];
$log="";
function fixTestProperty($originalArray,$probeArray) {
    global $log;
    $log.="\nINI fixTestProperty:";
    // $probeArray = [ [$key, $originalValue, $fixedValue], ... ];
    $newArray=[];
    $mergedKey=null;
    foreach ($originalArray as $key => $value) {
        $match=false;
        $log.="[$key] ";
        if (isset($mergedKey) && $key===$mergedKey) {
            $log.="MergedKey Found! $key => ".json_encode($value)."\n";
            $mergedKey=null;
            //continue;
            if (isset($newArray[$key])) $value=$newArray[$key];
            $match=true;
        }
        if (is_array($value)) {
            $newArray[$key]=fixTestProperty($value,$probeArray);
            continue;
        } else foreach ($probeArray as $idx => $data) {
            if ($key===$data[0]&&$value===$data[1]) {
                $log.="Key Found! $key => $value\n";
                if (isset($data[2])) {
                    $newArray[$key]=$data[2];
                    $match=true;
                    $log.="Assigned new value ".$data[2]."\n";
                    //continue 2;
                }
                if (isset($data[3]) && isset($data[4])) {
                    if (isset($originalArray[$data[3]])) {
                        $mergedKey=$data[3];
                        $log.="MergedKey=".$data[3]."\n";
                    }
                    $log.="Original array to Merge: ".json_encode($originalArray[$data[3]])."\n";
                    $log.="Merging array: ".json_encode($data[4])."\n";
                    if (!isset($data[2]))
                        $newArray[$key]=$value;
                    $newArray[$data[3]]=array_merge($originalArray[$data[3]],$data[4]);
                    $log.="Result array is ".json_encode($newArray[$data[3]])."\n";
                    $match=true;
                    //continue;
                }
                if (!$match) $log.="$idx) NO MATCH!\n";
            }
        }
        if (!$match) $newArray[$key]=$value;
    }
    return $newArray;
}
//echo json_encode(TABLA_FECHA)."<HR>";

//echo json_encode(array_merge(TABLA_FECHA,["eChilds"=>["eChilds"=>[["eChilds"=>[[],["eChilds"=>[["id"=>"fechaSolIniFld"]]]]],["eChilds"=>[[],["eChilds"=>[["id"=>"fechaSolFinFld"]]]]]]]]))."<HR>";
//echo json_encode(TABLA_FECHA+["eChilds"=>["eChilds"=>[["eChilds"=>[[],["eChilds"=>[["id"=>"fechaSolIniFld"]]]]],["eChilds"=>[[],["eChilds"=>[["id"=>"fechaSolFinFld"]]]]]]]])."<HR>";
*/
/*
function fixProperty($multiDimensionalArray,$originalProperty,$fixedProperty) {
    global $log;
    $newArray=[];
    $log.="BEGIN FIXING\n";
    foreach ($multiDimensionalArray as $key => $value) {
        $log.="Evaluate KEY $key:\n";
        if (isset($originalProperty[$key]) && $originalProperty[$key]===$value) {
            $log.="MATCH KEY ($key) - VALUE ($value)\n";
            $newArray[$key]=$fixedProperty[$key];
        } else if (is_array($value)) {
            $log.="PROBING NEXT LEVEL\n";
            $newArray[$key]=fixProperty($value,$originalProperty,$fixedProperty);
            $log.="PROBING COMPLETED\n";
        } else {
            $log.="NO MATCH, SIMPLE VALUE $key=$value\n";
            $newArray[$key]=$value;
        }
    }
    $log.="END FIXING\n";
    return $newArray;
}
echo json_encode(fixProperty(TABLA_FECHA,["id"=>"fechaIniFld"],["id"=>"fechaSolIniFld"]))."<HR>";
*/
//echo "<pre>$log</pre>";
//$log="";
/*
function fixProperty2($multiDimensionalArray,$fixingProperties) { // $fixingProperties = [ [$key, $originalValue, $fixValue], [$key, $originalValue, $fixValue], ... ]
    global $log;
    $newArray=[];
    $log.="BEGIN FIXING\n";
    foreach ($multiDimensionalArray as $key => $value) {
        $log.="Evaluate KEY $key:\n";
        $match=false;
        if (is_array($value)) {
            $log.="PROBING NEXT LEVEL\n";
            $newArray[$key]=fixProperty2($value,$fixingProperties);
            $log.="PROBING COMPLETED\n";
            continue;
        } else foreach ($fixingProperties as $idx => $data) {
            if ($key===$data[0] && $value===$data[1]) {
                $log.="MATCH KEY/VALUE '$key'/'$value'\n";
                $newArray[$key]=$data[2];
                $match=true;
                continue 2;
            }
        }
        $log.="NO MATCH, SIMPLE VALUE $key=$value\n";
        $newArray[$key]=$value;
    }
    $log.="END FIXING\n";
    return $newArray;
}
*/
//echo json_encode(fixProperty2(TABLA_FECHA,[["id","fechaIniFld","fechaSolIniFld"],["id","fechaFinFld","fechaSolFinFld"]]))."<HR>";
//echo json_encode(fixProperty2(TABLA_FECHA,[["id","fechaIniFld","fechaPagoIniFld"],["id","fechaFinFld","fechaPagoFinFld"]]))."<HR>";
//echo json_encode(fixProperty2(TABLA_FECHA,[["id","fechaIniFld","fechaFacIniFld"],["id","fechaFinFld","fechaFacFinFld"]]))."<HR>";
/*
$jsonFix=fixTestProperty(TABLA_LISTA,[["eText","Lista:","Alias:"],["src","imagenes/icons/add.png","../imagenes/icons/add.png"],["id","listFld","testFld","eChilds",[["eName"=>"OPTION","value"=>"1","eText"=>"Primero"],["eName"=>"OPTION","value"=>"2","eText"=>"Segundo"],["eName"=>"OPTION","value"=>"3","eText"=>"Tercero"]]]]);
$jsonFix2=fixTestProperty(TABLA_LISTA,[["eText","Lista:","Código:"],["src","imagenes/icons/add.png","../imagenes/icons/add.png"],["id","listFld","testFld","eChilds",[["eName"=>"OPTION","value"=>"A","eText"=>"Alpha"],["eName"=>"OPTION","value"=>"B","eText"=>"Beta"],["eName"=>"OPTION","value"=>"Z","eText"=>"Theta"]]],["eText","Todas","Todos"]]);
$jsonStr=json_encode($jsonFix);
$jsonStr2=json_encode($jsonFix2);
echo "<html><head><script>
function isElement(o){
  return (
    typeof HTMLElement === \"object\" ? o instanceof HTMLElement : //DOM2
    o && typeof o === \"object\" && o !== null && o.nodeType === 1 && typeof o.nodeName===\"string\"
);
}
function ecrea(props) {
    if (isElement(props)) return props;
    if (Array.isArray(props)) {
        const arr=[];
        props.forEach(function(elem) {arr.push(ecrea(elem));});
        return arr;
    }
    let propNames=Object.keys(props);
    if (props.eName) {
        let idx=propNames.indexOf(\"eName\");
        if (idx>=0) propNames.splice(idx,1);
        idx=propNames.indexOf(\"eText\");
        if (idx>=0) propNames.splice(idx,1);
        idx=propNames.indexOf(\"eChilds\");
        if (idx>=0) propNames.splice(idx,1);
        let newObj=document.createElement(props.eName);
        for(let i=0;i<propNames.length;i++) {
            newObj[propNames[i]]=props[propNames[i]];
        }
        if (props.eChilds) {
            if (Array.isArray(props.eChilds)) for (let i=0; i<props.eChilds.length; i++) {
                let child = ecrea(props.eChilds[i]);
                if (child) newObj.appendChild(child);
            } else {
                let child = ecrea(props.eChilds);
                if (child) newObj.appendChild(child);
            }
        } else if (props.eText) newObj.appendChild(document.createTextNode(props.eText));
        return newObj;
    } else if (props.eText) {
        let newObj=document.createTextNode(props.eText);
        return newObj;
    }
    return null;
}
function runTest() {
    document.body.appendChild(ecrea($jsonStr));
    document.body.appendChild(ecrea({eName:\"BR\"}));
    document.body.appendChild(ecrea($jsonStr2));
}
</script></head><body onload=\"runTest();\">";
echo "<pre>$log</pre><hr>";
echo $jsonStr."<hr>$jsonStr2<br>";
echo "</body></html>";
*/
