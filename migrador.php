<?php
/*
    Author: Armin
    Support: v0.1.0 ~ v0.2.7
    Conversion: v0.3.0

    Version: 0.1.0
    State: Beta
    Update: 28-05-2025
    Created: 27-05-2025

    *************************
    Convertir estructuras de versiones anteriores (v0.1.x / v0.2.x) a la nueva estructura JSON de v0.3.0.
    ⚠ Haga una copia de seguridad antes de ejecutar este script.
    *************************

    Copy:
        App/Database/ (v0.1.0 ~ v0.2.7)
            -   Publicaciones (v0.1.0+)
                -   Entries (v0.1.0+)
            -   Usuarios (v0.1.0+)
            -   Comentarios (v0.2.1+)
        
        Panel/App/ (v0.1.0 ~ v0.2.7)
            -   Config (v0.1.0+)
            -   Anuncios (v0.1.0+)
            -   Scripts_js + html (v0.1.0+)
            -   Htaccess (v0.1.0+)
            -   Creador/Creadores/Normal/Function/Lista-publicaciones (v0.2.3?)
            -   Templates ❌ (v0.2.2+)
            -   Themes ❌ (v0.1.1+)
        
        Database/Other/Count (v0.2.5+)
*/

$version = "v0.1.0 Beta 27/05/2025 ~ 28/05/2025";

if(!isset($_POST["continuar"]) || empty($_POST["continuar"])){
    echo "⚠️ ADVERTENCIA: Haga una copia de seguridad de tus archivos antes de continuar.<br><br>";
    echo "<form method='post'><input type='submit' name='continuar' value='Continuar' style='padding: 8px 10px;'></form>";
    echo "<small>{$version}</small>";
    exit;
}

define("RAIZ", __DIR__ ."/");
header('Content-Type: application/json');
class s {
    public function zona()
    {
        date_default_timezone_set('America/Bogota');
    }
    public function fecha()
    {
        $this->zona();
        return date('d/m/Y');
    }
    public function fecha_hora()
    {
        $this->zona();
        return date('d/m/Y - g:ia');
    }
    public function CrearCarpetas(string $ruta){
        if ($ruta[-1] != '/') { $ruta .= '/'; }
        if(!file_exists(RAIZ . $ruta)){
            if(mkdir(RAIZ . $ruta, 0777, true));
        }
    }
    public function Read(string $route){
        $route = "database/" . str_replace(".json", "", $route) . ".json";
        return json_decode(file_get_contents($route) ?? '', true) ?? [];
    }
    public function Save(string $route, array $data){
        $route = str_replace([".json", ".php"], "", $route) . ".json";
        return file_put_contents("database/" . $route, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    public function is_par_letras($numero){
        $letras = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "K", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];
        return ($numero % 2 == 0) ? $letras[rand(0, count($letras)-1)] : $numero;
    }
    public function GenerarPin(array $cantidad = [4, 5, 7]){
        $numeros = '';
        foreach ($cantidad as $key => $valor) {
            $numeros .= $key >= 1 ? '-' : '';
            for($i=0; $i < $valor; $i++){
                $numeros .= $this->is_par_letras(rand(0,9));
            }
        }
        return $numeros;
    }
    public function CreateEntry(string $route, string $directorio = "./"){
        $dir = __DIR__."/";
        $directorios = dirname($route) . "/";
        $explode = explode("/", $route);
        if (in_array($explode[0], ["app", "database", "assets", "process"])){
            return;
        }
        if(isset($explode[1])){
            if($explode[0] . "/" . $explode[1] == "admin/process"){ die("noooo"); return; }
        }
        if (!file_exists($dir.$directorios)){
            $this->CrearCarpetas($directorios);
        }
        $route = str_replace(".php", "", $route) . ".php";

        $guardar = '<?php # ' . $this->fecha_hora() . "\n";
        $guardar .= '$Web'." = ['directorio'=>'$directorio','ruta'=>'$route'];\n";
        $guardar .= "require_once ".'$Web'."['directorio'].'app/controller/controller.php';";
        return file_put_contents($dir.$route, $guardar);
    }
}
define("S", new s);
S->CrearCarpetas("database/post/");
S->CrearCarpetas("database/post/entries/");
S->CrearCarpetas("database/user/");
S->CrearCarpetas("database/comment/");
S->CrearCarpetas("database/files/html/");
S->CrearCarpetas("database/creator/");
S->CrearCarpetas("database/config/");

$confirm = [];

/*------------------------------------ POSTS ------------------------------------*/
$route_post = "app/database/publicaciones/";
$files = glob($route_post . "pu_*");
$ignore = [
    "pu_auth-cambiar-contrasena.php",
    "pu_auth-cambiar_contrasena.php",
    "pu_auth-configuracion.php",
    "pu_auth-iniciar.php",
    "pu_auth-olvide-contrasena.php",
    "pu_auth-olvide_contrasena.php",
    "pu_auth-registrar.php",
    "pu_error.php",
    "pu_p-perfil.php",
    "pu_p-perfiles.php",
    "pu_panel-index.php",
    "pu_panel-panel.php",
    "pu_reportar.php",
    "pu_search.php",
    "pu_bienvenida.php",
];

$file_save = [];

foreach ($files as $file) {
    if(!in_array(basename($file), $ignore)){
        $file_save[] = $file;
    }
}

foreach ($file_save as $file) {
    require $file;
    $ACR["db_archivo"] = substr($ACR["db_archivo"], 3, strlen($ACR["db_archivo"]));
    $ACR["db_archivo"] = str_replace(".php", "", $ACR["db_archivo"]) . ".json";
    $ACR["db_archivo"] = str_replace("_", "-", $ACR["db_archivo"]);
    $ACR["db_ruta"] = str_replace("_", "-", $ACR["db_ruta"]);
    $AC["archivo"] = str_replace("_", "-", $AC["archivo"]);

    if(isset($AC["referencia_anime"]) || isset($AC["referencia_hentai"]) || isset($AC["referencia"])){
        foreach (["_anime", "_hentai", ""] as $value) {
            if(isset($AC["referencia{$value}"]) && !empty($AC["referencia{$value}"])){
                $referencia = "referencia{$value}";
            }
        }
        $AC[$referencia] = str_replace(["pu_", ".php"], "", $AC[$referencia]) . ".json";
        $AC[$referencia] = str_replace("_", "-", $AC[$referencia]);
        $AC["referencia"] = $AC[$referencia];
        unset($AC["referencia_anime"]); unset($AC["referencia_hentai"]);
    }
    $confirm["post"][$ACR["db_archivo"]] = S->Save("post/" . $ACR["db_archivo"], ["ACR" => $ACR, "AC" => $AC]);
    S->CreateEntry($AC["ruta"].$AC["archivo"], $AC["directorio"]);
    unset($ACR); unset($AC);
}

/*------------------------------------ POSTS-ENTRIES ------------------------------------*/
$ruta_creator_entries = "panel/app/creador/creadores/normal/function/lista-publicaciones.php";
if(file_exists($ruta_creator_entries)){
    $confirm["list-of-entries"] = S->Save("creator/list-of-entries", require $ruta_creator_entries);
}

/*------------------------------------ ENTRIES ------------------------------------*/
$files = glob($route_post . "publicaciones*.php");
foreach ($files as $file) {
    $data = require $file;
    $name = (
        basename($file) == "publicaciones.php" ?
        str_replace("publicaciones", "posts", basename($file)) :
        str_replace("publicaciones-", "", basename($file))
    );
    $confirm["entries"][$name] = S->Save("post/entries/$name",
        $data
    );
}

/*------------------------------------ USERS ------------------------------------*/
foreach (["usuarios", "usuarios_extras"] as $file) {
    if(file_exists("app/database/usuarios/$file.php")){
        require "app/database/usuarios/$file.php";
        if(isset($usu) && $file == "usuarios"){
            foreach ($usu as $key => $value) {
                $usu[$value["id"]]["pin"] = S->GenerarPin();
                S->CreateEntry("p/".$usu[$value["id"]]["usuario"].".php", "../");
            }
        }
        $confirm["user"][$file] = S->Save("user/" . ($file == "usuarios" ? "user" : "extras"), $usu ?? []);
        unset($usu);
    }
}

/*------------------------------------ COMMENTS ------------------------------------*/
foreach (["comentarios", "comentarios_extras"] as $file) {
    if(file_exists("app/database/comentarios/$file.php")){
        if($file == "comentarios"){
            $comentarios = require "app/database/comentarios/$file.php";
        } else {
            require "app/database/comentarios/$file.php";
        }
        $confirm["comment"][$file] = S->Save("comment/" . ($file == "comentarios" ? "comment" : "extras"), $comentarios ?? []);
        unset($comentarios);
    }
}

/*------------------------------------ VISITS ------------------------------------*/
if (file_exists("database/other/count.json")){
    $confirm["visits"] = S->Save("other/visits", S->Read("other/count"));
    if($confirm["visits"]){ unlink("database/other/count.json"); }
}

/*------------------------------------ Settings ------------------------------------*/

$ruta = "panel/app/";
foreach (["anuncios", "config", "scripts_js", "htaccess"] as $file) {
    if(file_exists("{$ruta}$file/web-{$file}.php")){
        require "{$ruta}$file/web-{$file}.php";
        $new_file = match (true) {
            $file == "anuncios" => "ads",
            $file == "scripts_js" => "scripts",
            default => $file
        };
        if($file == "config"){
            $Web[$file]["language"] = isset($Web[$file]["language"]) ? $Web[$file]["language"] : "es";
        }
        if($file == "scripts_js"){
            $new_file = "scripts";
            $New = [];
            foreach ($Web[$file] as $key => $value) {
                $New[str_replace("_js", "", $key)] = $value;
            }
            $Web[$file] = $New;
            foreach (["font_awesome", "google", "otros"] as $value) {
                if(file_exists("{$ruta}$file/web-scripts_js_$value.html")){
                    $confirm["files-html"][$value] = file_put_contents("database/files/html/scripts_$value.html", file_get_contents("{$ruta}$file/web-scripts_js_$value.html"));
                }
            }
        }
        $data = S->Read("config/config") ?? [];
        $data[$new_file] = $Web[$file];
        $confirm["setting"][$new_file] = S->Save("config/config", $data);
    }
}

/*------------------------------------ Templates ------------------------------------*/
/*------------------------------------ Themes ------------------------------------*/

die(json_encode($confirm));