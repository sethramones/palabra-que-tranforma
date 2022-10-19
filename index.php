<?php
    session_start();
    include 'config.php';

    if (isset($_POST['submit'])) {

        $provider = $_POST["provider"];
        $title = $_POST["title"];
        $message = $_POST["message"];
        $post_id = '0';
        $link = $_POST['link'];
        $big_image = $_POST["image"];
        $unique_id = rand(1000, 9999);

        if ($provider == 'onesignal') {

            $content = array("en" =>  $message);

            $fields = array(
                'app_id' => $onesignal_app_id,
                'included_segments' => array('All'),                                            
                'data' => array(
                    "foo" => "bar",
                    "link" => $link,
                    "post_id" => $post_id,
                    "unique_id" => $unique_id
                ),
                'headings'=> array("en" => $title),
                'contents' => $content,
                'big_picture' => $big_image         
            );

            $fields = json_encode($fields);
            print("\nJSON sent:\n");
            print($fields);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                                                    'Authorization: Basic '. $onesignal_rest_api_key));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

            $response = curl_exec($ch);
            curl_close($ch);
            
            $_SESSION['msg'] = "Push notification sent...";
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;

        } else if ($provider == 'fcm') {
            sendFCM($unique_id, $title, $message, $big_image, $link, $post_id, $fcm_server_key, $fcm_topic);
        }

    }

    function sendFCM($unique_id, $title, $message, $big_image, $link, $post_id, $fcm_server_key, $fcm_topic) {
        $data = array(
            'to' => '/topics/' . $fcm_topic,
            'data' => array(
                'title' => $title,
                'message' => $message,
                'big_image' => $big_image,
                'link' => $link,
                'post_id' => $post_id,
                "unique_id"=> $unique_id
            )
        );

        $header = array(
            'Authorization: key=' . $fcm_server_key,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);

       if (curl_errno($ch)) {
           echo json_encode(false);
        } else {
           echo json_encode(true);
        }

        curl_close($ch);

        $_SESSION['msg'] = "Push notification sent...";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit; 

    }

?>

<!DOCTYPE html>
<html lang="en" class="h-100">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
        <meta name="generator" content="Hugo 0.84.0">
        <title>Your Single Radio - Push Notification</title>

        <link rel="icon" href="assets/images/favicon.png" type="image/x-icon">

        <!-- Font Awesome -->
        <link
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css"
          rel="stylesheet"/>
        <!-- Google Fonts -->
        <link
          href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap"
          rel="stylesheet"/>
        <!-- MDB -->
        <link
          href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.6.0/mdb.min.css"
          rel="stylesheet"/>

        <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">

        <style>
            #intro {
                background-color: #009788;
                height: 100vh;
            }

            /* Height for devices larger than 576px */
            @media (min-width: 992px) {
                #intro {
                  margin-top: -58.59px;
                }
            }

            .navbar .nav-link {
                color: #fff !important;
            }

            .poppins {
                  font-family: 'Poppins', sans-serif;
            }
        </style>

    </head>

    <body class="poppins">

        <!--Main Navigation-->
  <header>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark d-none d-lg-block" style="z-index: 2000;">
      <div class="container-fluid">
        <!-- Navbar brand -->
        <a class="navbar-brand nav-link" href="./">
          Your Single Radio - Push Notification
        </a>
        <button class="navbar-toggler" type="button" data-mdb-toggle="collapse" data-mdb-target="#navbarExample01"
          aria-controls="navbarExample01" aria-expanded="false" aria-label="Toggle navigation">
          <i class="fas fa-bars"></i>
        </button>

      </div>
    </nav>
    <!-- Navbar -->

    <!-- Background image -->
    <div id="intro" class="bg-image shadow-2-strong">
      <div class="mask d-flex align-items-center h-100" >
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-xl-5 col-md-8">
              <form method="post" class="bg-white  rounded-5 shadow-5-strong p-5">

                <div class="mb-4">
                    <h3>Push Notification</h3>
                </div>

                <?php if(isset($_SESSION['msg'])) { ?>
                    <div class="mb-3 alert alert-success" role="alert">
                        <?php echo $_SESSION['msg']; ?>
                    </div>
                <?php unset($_SESSION['msg']); }?>

                <div class="mb-4">
                    <select class="form-select" name="provider">
                        <option value="fcm">FCM</option>
                        <option value="onesignal">OneSignal</option>
                    </select>
                </div>

                <div class="form-outline mb-4">
                    <input type="text" name="title" id="title" class="form-control" required/>
                    <label class="form-label" for="title">Title</label>
                </div>

                <div class="form-outline mb-4">
                    <textarea class="form-control" name="message" id="message" rows="2" required></textarea>
                    <label class="form-label" for="message">Message</label>
                </div>

                <div class="form-outline mb-4">
                    <input type="text" name="image" id="image" class="form-control" required />
                    <label class="form-label" for="image">Image URL</label>
                </div>

                <div class="form-outline mb-4">
                    <input type="text" name="link" id="link" class="form-control" />
                    <label class="form-label" for="link">Link (Optional)</label>
                </div>

                <div align="right">
                    <button type="submit" name="submit" class="btn btn-primary btn-lg btn-rounded">Send Notification</button>
                </div>

              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Background image -->
  </header>
  <!--Main Navigation-->

    <!-- MDB -->
    <script
      type="text/javascript"
      src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.6.0/mdb.min.js"
    ></script>

    </body>

</html>