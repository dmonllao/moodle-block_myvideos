<?php // $Id: block_myvideos.php,v 1.4 2010/11/09 11:55:59 davmon Exp $

$string['accessdenied'] = 'Accés denegat';
$string['addtofavorites'] = 'afegir a favorits';
$string['allowcomments'] = 'Permetre comentaris';
$string['areyousure'] = 'Estàs segur?';
$string['cantbeadded'] = 'No es pot afegir';
$string['cantbemodified'] = 'No es pot modificar';
$string['commentadded'] = 'Comentari afegit';
$string['comments'] = 'Comentaris';
$string['download'] = 'Descarrega';
$string['errorcant'] = 'No, no podem';
$string['errorcantdelete'] = 'No es pot eliminar';
$string['errorcantsave'] = 'No es pot guardar';
$string['errorcheckpermissions'] = 'Comprova els permissos d\'escriptura de la variable de configuració moodlepath o canvia la configuració del bloc';
$string['errorcourseid'] = 'El id del curs no és correcte';
$string['errorffmpegconnecting'] = 'Error connectant al servidor de ffmpeg';
$string['errorffmpegfile'] = 'Error d\'arxiu';
$string['errorffmpegfiletype'] = 'El format de l\'arxiu no és correcte';
$string['errorffmpeglogin'] = 'Error de validació al servidor de ffmpeg';
$string['errorffmpegsending'] = 'Error enviant el video a codificar';
$string['errorffmpegrecieving'] = 'No es poden rebre els arxius codificats';
$string['errorinserting'] = 'Error al insertar a la base de dades';
$string['errornoid'] = 'No s\'ha especificat cap id';
$string['errornosize'] = 'No s\'ha pogut trobar el tamany del video';
$string['errornossh'] = 'És necessari instal·lar la extensió SSH2 de PHP per poder codificar videos'; 
$string['errorwrongsesskey'] = 'Clau de sessió incorrecta';
$string['errorwrongvideoid'] = 'L\'id del video no és correcte';
$string['favoritevideoslabel'] = 'Videos favorits';
$string['fileconversionerror'] = 'Error de conversió';
$string['filetoupload'] = 'Arxiu a pujar';
$string['filter'] = 'Filtrar vídeos';
$string['getembedcode'] = '&lt;Embed&gt;';
$string['keywords'] = 'Paraules clau';
$string['loading'] = 'Carregant';
$string['myvideos:favoritevideo'] = 'Afegir a favorits';
$string['myvideos:linkvideo'] = 'Enllaçar un video extern';
$string['myvideos:manage'] = 'Utilitzar el bloc';
$string['myvideos:publicvideo'] = 'Fer accessibles els videos des de fora de Moodle';
$string['myvideos:selectquality'] = 'Selecciona la qualitat del video codificat';
$string['myvideos:uploadvideo'] = 'Pujar un video';
$string['noblockconfig'] = 'No s\'ha configurat el bloc';
$string['noresults'] = 'No hi ha resultats';
$string['publiclevel'] = 'Privacitat';
$string['quality'] = 'Qualitat';
$string['qualityhigh'] = 'Alta';
$string['qualitylow'] = 'Baixa';
$string['qualitymedium'] = 'Mitjana';
$string['searchdescription'] = 'Buscar també a la descripció dels videos';
$string['settingffmpeg'] = 'Ruta a ffmpeg';
$string['settingffmpegdesc'] = 'Ruta de l\'executable de conversió ffmpeg. Pot ser ffmepg tot i que es troba en desús. S\'aconsella emprar el seu reemplaç avconv.';
$string['settingmencoder'] = 'Ruta a mencoder'; 
$string['settingmoodlepath'] = 'Ruta per desar els fitxers codificats';
$string['settingpassword'] = 'Clau d\'accés de l\'usuari';
$string['settingpath'] = 'Ruta on desar els fitxers a codificar (després s\'eliminen)';
$string['settingserver'] = 'Servidor d\'ffmpeg';
$string['settingusername'] = 'Nom d\'usuari per connectar al servidor';
$string['terms'] = 'Termes d\'ús';
$string['termsok'] = 'Accepto els temes d\'ús';
$string['termstext'] = 'a) El contingut que estàs a punt de publicar en aquesta secció, té l\'objectiu exclusiu d\'estar destinat a la docència. '.chr(13).chr(10).'
b) El contingut protegit pels drets d\'autor de tercers únicament podrà ser usat amb el consentiment del titular. Si és el cas, se\'n farà referència de l\'autoria omplint el camp \"Autor\" del formulari d\'aquesta pàgina.'.chr(13).chr(10).'
c) En cap cas es podran publicar documents audiovisuals que continguin contingut pornogràfic o sexe explícit, ni aquells amb contingut amenaçador o que inciti a la violència, al racisme, la xenofòbia o a la discriminació de qualsevol tipus. Tampoc es podrà publicar material protegit per la normativa de drets d\'autor, ni amb caràcter difamatori.'.chr(13).chr(10).'
d) Vostè reconeix i accepta ser l\'únic responsable de l\'arxiu i de les conseqüències de la seva publicació.'.chr(13).chr(10).'
e) Aquest lloc web no és responsable dels arxius publicats en aquesta secció.'.chr(13).chr(10).'
f) Aquest lloc web es reserva el dret d\'emprendre les accions legals oportunes en el cas d\'incompliment legal o que existeixen fets constitutius de delicte.'.chr(13).chr(10).'
g) El fet de prémer el botó \"Pujar el vídeo\" implica la conformitat d\'aquestes condicions d\'ús.';
$string['therearenovideos'] = 'No hi ha videos';
$string['thumberror'] = 'Error creant la imatge de previsualització';
$string['title'] = 'Els meus videos';
$string['titledeletefavoritevideo'] = 'Eliminar video favorit';
$string['titledeletevideo'] = 'Eliminar video';
$string['titleeditvideo'] = 'Editar video';
$string['titlelinkvideo'] = 'Enllaçar video';
$string['titlesearchvideos'] = 'Buscar videos';
$string['titleuploadvideo'] = 'Pujar video';
$string['titlevideo'] = 'Video';
$string['titlevideos'] = 'Els meus videos';
$string['titleviewvideo'] = 'Veure video';
$string['unknown'] = 'desconegut';
$string['uservideoslabel'] = 'Videos d\'usuari';
$string['videoadded'] = 'Video afegit';
$string['videoauthor'] = 'Autor';
$string['videodescription'] = 'Descripció';
$string['videoembeddedcode'] = 'Codi incrustat del video';
$string['videotags'] = 'Etiquetes';
$string['videotitle'] = 'Títol';
$string['views'] = 'Visualitzacions';
$string['visiblemoodle'] = 'Només a Moodle';
$string['visibleprivate'] = 'Privat';
$string['visiblepublic'] = 'Públic';
$string['younovideos'] = 'No tens cap videos';

?>