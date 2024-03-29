<?php
    use Blindtest\Services\BlindtestService;
    use Blindtest\Services\MusicService;
    use Blindtest\Controllers\Security;

    require_once __DIR__ . '/Service/BlindtestService.php';
    require_once __DIR__ . '/Service/MusicService.php';
    require_once __DIR__ . '/Controllers/Security.class.php';

    $blindtestservice = new BlindtestService();
    $musicservice = new MusicService();
    $security = new Security();
    session_start();

    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['blindtest']) === true){
        $data = json_decode(file_get_contents('php://input'), true);
        $data = Security::secureArray($data);
        $roundConfig = $_SESSION['blindtest']['rounds']['config'];

        switch($data['dataParams']){
            case 'play':
                if($_SESSION['blindtest']['timer']['ongoing'] === false && is_int($data['timeleft'])){
                    if($_SESSION['blindtest']['timer']['left'] != $data['timeleft']){
                        $data['timeleft'] = $_SESSION['blindtest']['timer']['left'];
                    }

                    if($_SESSION['blindtest']['rounds']['actual'] === 1 && $_SESSION['blindtest']['music']['sample'] === NULL){
                        $musicstart = $_SESSION['blindtest']['music'][0];
                        $file = $musicstart['file'];
                        $genre = $musicstart['idgenre'];
                        $type = $musicstart['idtype'];
                        $timer = $_SESSION['blindtest']['timer']['config'];
                        
                        $result = $musicservice->getMusicFile($file, $genre, $type, $timer);
                        $_SESSION['blindtest']['music']['sample'] = $result;
                    }

                    if($blindtestservice->checkTimestamp($data['timeleft']) === true){
                        $audio = $_SESSION['blindtest']['music']['sample'];
                        $actualround = $_SESSION['blindtest']['rounds']['actual'];
                        
                        if($data['timeleft'] === 0){
                            $_SESSION['blindtest']['timer']['left'] = $_SESSION['blindtest']['timer']['config'];
                            $_SESSION['blindtest']['timer']['ongoing'] = true;
                            
                            echo json_encode(['success' => true, 'roundconfig' => $roundConfig, 'roundactual' => NULL, 'timeleft' => $_SESSION['blindtest']['timer']['config'], 'input' => $data['dataParams'], 'data' => $_SESSION['blindtest']['music'][$actualround - 1], 'audio' => $audio]);
                        }else{
                            $_SESSION['blindtest']['timer']['left'] = $data['timeleft'];
                            $_SESSION['blindtest']['timer']['ongoing'] = true;

                            echo json_encode(['success' => true, 'roundconfig' => $roundConfig, 'roundactual' => NULL, 'timeleft' => $_SESSION['blindtest']['timer']['left'], 'input' => $data['dataParams'], 'data' => $_SESSION['blindtest']['music'][$actualround - 1], 'audio' => $audio]);
                        }
                    }else{
                        echo json_encode(['success' => false, 'roundconfig' => $roundConfig, 'roundactual' => NULL, 'timeleft' => $_SESSION['blindtest']['timer']['left'], 'input' => $data['dataParams'], 'data' => NULL]);
                    }
                }else{
                    echo json_encode(['success' => false, 'roundconfig' => NULL, 'roundactual' => NULL, 'timeleft' => $_SESSION['blindtest']['timer']['left'], 'input' => $data['dataParams'], 'data' => NULL]);
                }
                break;
            case 'restart':
                if($_SESSION['blindtest']['music']['sample'] != NULL && $_SESSION['blindtest']['timer']['ongoing'] === false){
                    $_SESSION['blindtest']['timer']['left'] = $_SESSION['blindtest']['timer']['config'];
                    $_SESSION['blindtest']['rounds']['actual'] = 1;
                    $_SESSION['blindtest']['music']['sample'] = NULL;
                    $roundstart = $_SESSION['blindtest']['rounds']['actual'];
                    echo json_encode(['success' => true, 'roundconfig' => $roundConfig, 'roundactual' => $roundstart, 'timeleft' => $_SESSION['blindtest']['timer']['config'], 'input' => $data['dataParams'], 'data' => $_SESSION['blindtest']['music'][$roundstart - 1], 'audio' => NULL]);    
                }else{
                    echo json_encode(['success' => false, 'roundconfig' => NULL, 'roundactual' => NULL, 'timeleft' => $_SESSION['blindtest']['timer']['config'], 'input' => $data['dataParams'], 'data' => NULL, 'audio' => NULL]);
                }
                break;
            case 'next':
                if($_SESSION['blindtest']['rounds']['actual'] < $_SESSION['blindtest']['rounds']['config'] && $_SESSION['blindtest']['timer']['ongoing'] === false && $_SESSION['blindtest']['music']['sample'] != NULL){
                    $roundactual = $_SESSION['blindtest']['rounds']['actual'];
                    $_SESSION['blindtest']['rounds']['actual'] = $roundactual + 1;
                    $nextround = $_SESSION['blindtest']['rounds']['actual'];
                    $nextmusic = $_SESSION['blindtest']['music'][$nextround - 1];

                    $file = $nextmusic['file'];
                    $genre = $nextmusic['idgenre'];
                    $type = $nextmusic['idtype'];
                    $timer = $_SESSION['blindtest']['timer']['config'];
                    
                    $result = $musicservice->getMusicFile($file, $genre, $type, $timer);
                    $_SESSION['blindtest']['music']['sample'] = $result;

                    echo json_encode(['success' => true, 'roundconfig' => $roundConfig, 'roundactual' => $nextround, 'timeleft' => $_SESSION['blindtest']['timer']['config'], 'input' => $data['dataParams'], 'data' => $nextmusic, 'audio' => $_SESSION['blindtest']['music']['sample']]);
                }else{
                    echo json_encode(['success' => false, 'roundconfig' => NULL, 'roundactual' => NULL, 'timeleft' => $_SESSION['blindtest']['timer']['left'], 'input' => $data['dataParams'], 'data' => NULL, 'audio' => NULL]);    
                }
                break;
            case 'previous':
                if($_SESSION['blindtest']['rounds']['actual'] > 1 && $_SESSION['blindtest']['timer']['ongoing'] === false && $_SESSION['blindtest']['music']['sample'] != NULL){
                    $roundactual = $_SESSION['blindtest']['rounds']['actual'];
                    $_SESSION['blindtest']['rounds']['actual'] = $roundactual -1;
                    $previousround = $_SESSION['blindtest']['rounds']['actual'];
                    $previousmusic = $_SESSION['blindtest']['music'][$previousround - 1];

                    $file = $previousmusic['file'];
                    $genre = $previousmusic['idgenre'];
                    $type = $previousmusic['idtype'];
                    $timer = $_SESSION['blindtest']['timer']['config'];
                    
                    $result = $musicservice->getMusicFile($file, $genre, $type, $timer);
                    $_SESSION['blindtest']['music']['sample'] = $result;

                    echo json_encode(['success' => true, 'roundconfig' => $roundConfig, 'roundactual' => $previousround, 'timeleft' => $_SESSION['blindtest']['timer']['config'], 'input' => $data['dataParams'], 'data' => $previousmusic, 'audio' => $_SESSION['blindtest']['music']['sample']]);
                }else{
                    echo json_encode(['success' => false, 'roundconfig' => NULL, 'roundactual' => NULL, 'timeleft' => $_SESSION['blindtest']['timer']['left'], 'input' => $data['dataParams'], 'data' => NULL, 'audio' => NULL]);    
                }
                break;
            case 'result':
                if($_SESSION['blindtest']['timer']['ongoing'] === false && $_SESSION['blindtest']['music']['sample'] != NULL){
                    $roundactual = $_SESSION['blindtest']['rounds']['actual'];
                    echo json_encode(['success' => true, 'roundconfig' => $roundConfig, 'roundactual' => NULL, 'timeleft' => $_SESSION['blindtest']['timer']['config'], 'input' => $data['dataParams'], 'data' => $_SESSION['blindtest']['music'][$roundactual - 1], 'audio' => NULL]);
                }else{
                    echo json_encode(['success' => false, 'roundconfig' => NULL, 'roundactual' => NULL, 'timeleft' => $_SESSION['blindtest']['timer']['left'], 'input' => $data['dataParams'], 'data' => NULL, 'audio' => NULL]);
                }
                break;
            case 'endtimer':
                if($data['timeleft'] == 0 && is_int($data['timeleft'])){
                    $_SESSION['blindtest']['timer']['left'] = $data['timeleft'];
                    $_SESSION['blindtest']['timer']['ongoing'] = false;
                    $roundActual = $_SESSION['blindtest']['rounds']['actual'];
                    echo json_encode(['success' => true, 'roundconfig' => $roundConfig, 'roundactual' => $roundActual, 'timeleft' => $_SESSION['blindtest']['timer']['left'], 'input' => $data['dataParams'], 'data' => NULL]);
                }
                break;
            case 'quit':
                session_destroy();
                echo json_encode(['disconnected' => true]);
                break;
            default: throw new RuntimeException("No valid command.");
        }
    }