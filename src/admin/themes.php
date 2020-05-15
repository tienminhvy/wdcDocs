<?php 
    session_start();
    define('isSet', 1);
    define('setting',1);
    require_once('settings.php');
    require_once('db_connect.php');
    if (!isset($installed)) {
        die("You must run the installation file (install.php) in this directory before continue!");
    }
    require_once('../loginCheck.php');
    loginCheck($wdc_id, $wdc_token, $db, true);
?>
<?php 
    // lấy dữ liệu từ url
    $fileToOpen = $_GET['file'];
    // Xử lý đầu vào, tránh vấn đề bảo mật
    if (isset($fileToOpen)) {
        $fileToOpen = str_replace('http://', '', $fileToOpen);
        $fileToOpen = str_replace('https://', '', $fileToOpen);
        $fileToOpen = str_replace('HTTP://', '', $fileToOpen);
        $fileToOpen = str_replace('HTTPS://', '', $fileToOpen);
        $fileToOpen = str_replace('://', '', $fileToOpen);
        $fileToOpen = str_replace('//', '', $fileToOpen);
    }
    $selectedTheme = $_GET['theme'];
    // từ admin sang ổ themes
    chdir('../themes');
    // thu thập danh sách thư mục
    $dirs = glob('*', GLOB_ONLYDIR);
    foreach ($dirs as $value) {
        // các theme có trong themes
        $themeChangerOption .= 
        "<option value='$value'>$value</option>";
    }
    // nếu đổi theme thì
    if (isset($_GET['theme'])){
        foreach ($dirs as $value) {
            if ($value==$_GET['theme']) {
                $isThemeFound = true;
                break;
            } else {
                $isThemeFound = false;
            }
        }
        if ($isThemeFound) {
            $selectedTheme = $_GET['theme'];
        } else {
            $selectedTheme = $dirs[0];
        }
    } else {
        $selectedTheme = $dirs[0];
    }
    // chuyển sang ổ theme
    chdir($selectedTheme);
    // định nghĩa hằng đường dẫn tuyệt đối
    define('ABSOLUTEPATH', getcwd());
    // quay lại vị trí ban đầu
    chdir('..');
    // hàm xử lý in cây thư mục và tệp tin
    function getList($dirName, $index = 0)
    {
        if ($index>0) { // vị trí từ 1 trở về sau
            $prefix .= str_repeat('-', $index);
        } else { // nếu ở vị trí đầu tiên
            $prefix = '';
        }
        chdir("$dirName"); // chuyển ổ sang ổ theme
        $GLOBALS['getList'] .= "Dir: $prefix $dirName";
        $files = glob('*.*'); // lấy tất cả file
        foreach ($files as $value) {
            $absolutePath = getcwd().'\\'; // địa chỉ tuyệt đối
            $relativePath = substr(str_replace(ABSOLUTEPATH,'',$absolutePath), 1); // tương đối
            $GLOBALS['getList'] .= "<div class='card-body'>
                    <div class='list-group'>";
                                                                                                                // mã hoá url
            $GLOBALS['getList'] .= "<a class='list-group-item list-group-item-action' href='themes.php?file=".urlencode("$relativePath$value")."&theme=$dirName'>$value</a>";
            $GLOBALS['getList'] .= "</div></div>";
        }
        ++$index; // tăng index
        $dirs = glob('*', GLOB_ONLYDIR); // lấy tất cả directory
        foreach ($dirs as $value) { // lặp
            getList($value, $index); // đệ quy
            chdir('..'); // sau khi đệ quy, quay về ổ đĩa trước
        }
    }
    getList($selectedTheme); // thực thi hàm
    // nếu request mở file hợp lệ
    if (isset($fileToOpen)){
        // thao tác đọc ghi và đóng file
        @$fileForProcess = fopen("$fileToOpen",'r');
        if (!$fileForProcess) {
            $notify = "Cannot open the specify file, file not found!";
            $requestReturn = 'error';
            $fileNotFound = true;
        } else {
            @$fileOpended = fread($fileForProcess, filesize("$fileToOpen"));
            fclose($fileForProcess);
            $fileNotFound = false;
        }
    } else { // mở file mặc định (info.php)
        @$fileForProcess = fopen("info.php",'r');
        if (!$fileForProcess) {
            $notify = "Cannot open the specify file, file not found!";
            $requestReturn = 'error';
            $fileNotFound = true;
        } else {
            @$fileOpended = fread($fileForProcess, filesize("info.php"));
            fclose($fileForProcess);
            $fileNotFound = false;
        }
    }
    $valFromUser = $_POST['codemirrorValueFromUser']; // dữ liệu từ người dùng nhập vào
    $fileIsEdited = false; // file đã chỉnh sửa mặc định là false
    if (isset($valFromUser) && isset($fileToOpen) && $fileNotFound == false) { // khi user xác nhận file cần chỉnh và đã nhập dữ liệu
        $fileIsEdited = true; // file đã chỉnh là true
        $fileForProcess = fopen("$fileToOpen",'w+');
        fwrite($fileForProcess, "$valFromUser");
        fclose($fileForProcess);
        // thông báo thành công
        $notify = "Edit file successfully";
        $requestReturn = 'success';
    } elseif (isset($valFromUser) && $fileNotFound == false) { // hoặc user đã chỉnh sửa file mặc định
        $fileIsEdited = true;
        $fileForProcess = fopen("info.php",'w+');
        fwrite($fileForProcess, "$valFromUser");
        fclose($fileForProcess);
        $notify = "Edit file successfully";
        $requestReturn = 'success';
    }
    if ($fileIsEdited) { // nếu file đã chỉnh sửa
        $fileOpended = $valFromUser; // lấy dữ liệu từ user
    }
    $file_parts = pathinfo("$fileToOpen"); // lấy thông tin của file đang mở

    switch($file_parts['extension'])
    { // mode của codemirror
        case "css": // trường hợp là file css
            $codemirrorMode = 'text/css';
        break;

        case "js": // trường hợp file js
            $codemirrorMode = 'text/javascript';
        break;

        default: // còn lại
            $codemirrorMode = 'application/x-httpd-php';
        break;
    }
    // tính năng thử nghiệm
    // if (isset($fileToOpen)) { // nếu user xác định file cần mở
    //     $checkToActive = "$fileToOpen";
    // } else {
    //     $fileToOpen = 'info.php';
    // }
    
?>
<?php 
    function notifyTemplate($notify, $type='')
    {
        if (isset($notify)) {
            switch ($type) {
                case 'success':
                    return "<div class='alert alert-success' role='alert'>$notify</div>";
                    break;

                case 'error':
                    return "<div class='alert alert-danger' role='alert'>$notify</div>";
                    break;

                case 'warning':
                    return "<div class='alert alert-warning' role='alert'>$notify</div>";
                    break;
                
                default:
                    return "<div class='alert alert-primary' role='alert'>$notify</div>";
                    break;
            }
        }
        return;
    }
    switch ($requestReturn) {
        case 'success':
            $notifyMsg = notifyTemplate($notify, 'success');
            break;

        case 'error':
            $notifyMsg = notifyTemplate($notify, 'error');
            break;

        case 'warning':
            $notifyMsg = notifyTemplate($notify, 'warning');
            break;
        
        default:
            $notifyMsg = notifyTemplate($notify);
            break;
    }
    $css =
    "<style>
    #codemirror {
        height: auto;
    }
    #textareaCodemirror {
        display: none;
    }
    </style>";
    $js =
    "<script>
    $(function () {
        codemirror = document.getElementById('codemirror');
        var myCodeMirror = CodeMirror(codemirror, {
            value: `$fileOpended`,
            lineNumbers: true,
            mode:  '$codemirrorMode',
            extraKeys: {'Ctrl-Space': 'autocomplete'},
            theme: 'monokai',
        });  
        let issubmit = false;
        myCodeMirror.setSize('100%', 'auto');
        $('#fileSubmitBtn').on('click', function() {
            $('#textareaCodemirror').html(myCodeMirror.getValue());
            $('#editFileForm').submit();
        });
        
        // Form Submit
        $(document).on('submit', 'form', function(event){
            // disable unload warning
            issubmit = true;
            $(window).off('beforeunload');
        });
        $(`#themeChanger [value='$selectedTheme']`).attr('selected', 'selected');
        codemirrorValue = myCodeMirror.getValue();
        setInterval(function(){ 
            if (myCodeMirror.getValue()!=codemirrorValue&&issubmit==false) {
                $(window).on('beforeunload', function(){
                    return 'Any changes will be lost';
                });
            } else {
                $(window).off('beforeunload');
            }
        }, 100);
    });
        </script>";
    $themeChanger = 
    "<select name='theme' class='form-control' id='themeChanger'>$themeChangerOption</select>";
    $codemirrorPHP =
    "<script src='services/codemirror/lib/codemirror.js'></script>
    <link rel='stylesheet' href='services/codemirror/lib/codemirror.css'>
    <script src='services/codemirror/mode/php/php.js'></script>
    <script src='services/codemirror/addon/hint/show-hint.js'></script>
    <link rel='stylesheet' href='services/codemirror/addon/hint/show-hint.css'>
    <script src='services/codemirror/mode/clike/clike.js'></script>
    <script src='services/codemirror/mode/htmlmixed/htmlmixed.js'></script>
    <script src='services/codemirror/mode/css/css.js'></script>
    <script src='services/codemirror/mode/css/css.js'></script>
    <script src='services/codemirror/mode/xml/xml.js'></script>
    <script src='services/codemirror/mode/javascript/javascript.js'></script>
    <link rel='stylesheet' href='services/codemirror/theme/monokai.css'>";
    require_once('themes/default/header.php');
    echo $css;
    require_once('themes/default/modules/mainMenus.php');
    echo $codemirrorPHP;
    echo "<main>
        <div class='container'>
            <div class='row'>
                <div class='col'><h2 class='text-center'>Edit theme</h2></div>
            </div>
            <div class='row'>
                <div class='col-8'>
                    <div class='alert alert-warning' role='alert'>
                        Please double-check the edited file for error
                        before saving the content. This software currently do not support the 
                        PHP Complier features.
                    </div>
                    $notifyMsg
                    <form method='POST' id='editFileForm'>
                        <div id='codemirror'></div>
                        <textarea id='textareaCodemirror' name='codemirrorValueFromUser'></textarea>
                        <div id='fileSubmitBtn' class='btn btn-primary'>Save</div>
                    </form>
                </div>
                <div class='col-4'>
                    <form method='GET' id='changeTheme'>
                        <h5>Change theme</h5>
                        $themeChanger
                        <button class='btn btn-info btn-block' id='btnChangeTheme'>Submit</button>
                    </form>
                    $getList
                </div>
            </div>
        </div>
    </main>";
    require_once('themes/default/footer.php');
    echo $js;
?>