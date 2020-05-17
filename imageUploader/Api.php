<?php 
 
    //getting database connection
    require_once 'DbConnect.php';
    
    //array to show the response
    $response = array();
    
    //uploads directory, we will upload all the files inside this folder
    $target_dir = "uploads/";
 
    //checking if we are having an api call, using the get parameters 'apicall'
    if(isset($_GET['apicall'])){
		
		switch($_GET['apicall']){
            
            //if the api call is for uploading the image 
            case 'upload':
                //error message and error flag
                $message = 'Params ';
                $is_error = false; 
                
                //validating the request to check if all the required parameters are available or not 
                if(!isset($_POST['desc'])){
                    $message .= "desc, ";
                    $is_error = true; 
                }
 
                if(!isset($_FILES['image']['name'])){
                    $message .= "image ";
                    $is_error = true; 
                }
                
                //in case we have an error in validation, displaying the error message 
                if($is_error){
                    $response['error'] = true; 
                    $response['message'] = $message . " required."; 
                }else{
                    //if validation succeeds 
                    //creating a target file with a unique name, so that for every upload we create a unique file in our server
                    $target_file = $target_dir . uniqid() . '.'.pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                   // echo $target_file;
                   if(move_uploaded_file($_FILES['image']['tmp_name'],$target_file)){

                    $stmt = $conn->prepare("INSERT INTO uploads (`path`, `description`) VALUES (?, ?)");
                    $stmt->bind_param("ss", $target_file, $_POST['desc']); 
                  
                    if($stmt->execute()){
                        $response = false;
                        $response['message'] = "image uploaded successfully ...";
                        $response['image'] = getBaseURL() . $target_file;
                    }
                }
                   else{
                       $response = true;
                       $response['message']="try again later ...";

                   }
                }
            break;

            case 'images':
                $stmt = $conn->prepare("SELECT `id`, `path`, `description` FROM uploads");
                $stmt->execute();
                $stmt->bind_result($id, $path, $desc);
                
                while($stmt->fetch()){
                    $image = array(); 
                    $image['id'] = $id; 
                    $image['path'] = getBaseURL() . $path; 
                    $image['desc'] = $desc;
                    array_push($response, $image);
                }

            break; 
			
			default: 
				$response['error'] = true; 
				$response['message'] = 'Invalid Operation Called';
        } 
    }  
    
    function getBaseURL(){
        $url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $url .= $_SERVER['SERVER_NAME'];
        $url .= $_SERVER['REQUEST_URI'];
        return dirname($url) . '/';
    }

    header('Content-Type: application/json');
    echo json_encode($response);