<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <style>
        body{
            padding: 7px 10px;
            color: #fff;
            background: #111;
        }
        a{
            font-size: 18px;
        }
        *{
            color: #fff;
        }
    </style>
</head>
<body>
    <?php
        $action = trim(getenv('ORIG_PATH_INFO') ?: getenv('PATH_INFO'), '/');
        $conn = mysqli_connect("localhost", "u7428_tommydev", "20080121", "u7428_tommydev");
        if (mysqli_error($conn)) {
            echo mysqli_error($conn);
        }else{
            // echo "success";
        }
        $quiz_id = $action;
        $result = mysqli_query($conn, "SELECT * FROM answers WHERE id > 0 AND quiz_id = $quiz_id");
        if ($result->num_rows) {
            $rating = array();
            foreach ($result as $key => $value) {
                if (!isset($rating[$value["fromid"]]["correctAnswers"])) {
                    $rating[$value["fromid"]]["correctAnswers"] = 0;
                }
                if (!isset($rating[$value["fromid"]]["inCorrectAnswers"])) {
                    $rating[$value["fromid"]]["inCorrectAnswers"] = 0;
                }
                if (boolval($value['answer'])) {
                    $rating[$value["fromid"]]["correctAnswers"] += 1;
                }else{
                    $rating[$value["fromid"]]["inCorrectAnswers"] += 1;
                }
                $rating[$value["fromid"]]["quizCount"] = $rating[$value["fromid"]]["inCorrectAnswers"] + $rating[$value["fromid"]]["correctAnswers"];
            }
            $finalRating = array();
            foreach ($rating as $key => $value) {
                $getUsers = mysqli_query($conn, "SELECT * FROM quiz_users WHERE fromid = $key");
                // $db->selectWhere('quiz_users',[
                //     [
                //         'fromid' => trim($key),
                //         'cn' => '='
                //     ],
                // ]);
                if ($getUsers->num_rows) {
                    $oneData = array();
                    foreach ($getUsers as $keys => $val) $oneData[] = $val;
                    $finalRating[] = array('user' => $oneData,'fromid' => $key, 'correctAnswers' => $value['correctAnswers'],'inCorrectAnswers' => $value['inCorrectAnswers'], 'quizCount' => $value['quizCount']);
                }
            }
            usort($finalRating, function ($a, $b) {
                if ($a['correctAnswers'] == $b['correctAnswers']) return 0;
                return $a['correctAnswers'] < $b['correctAnswers'] ? 1 : -1;
            });
        }
        $quiz = mysqli_query($conn, "SELECT * FROM quiz WHERE id = $quiz_id");
        if ($quiz->num_rows) {
            $quiz_data = mysqli_fetch_assoc($quiz);
            $solvedRating = "<b style='font-size: 20px;'>Test nomi:</b> $quiz_data[quiz_title]<br><b style='font-size: 20px;'>Test tavsifi: </b>$quiz_data[quiz_desc]<br><br>";
            $i = 0;
            $correct = "";
            $uncorrect = "";
            foreach ($finalRating as $key => $value) {
                $i++;
                foreach ($value['user'] as $userKey => $userVal) {
                    $solvedRating .= "$i) <a href='tg://user?id=".$userVal['fromid']."'>".$userVal['name']."</a>";
                    $answersChoose = mysqli_query($conn, "SELECT * FROM answers WHERE fromid = ".$userVal['fromid']);
                    // $userChooseAnsw = mysqli_fetch_assoc($answersChoose);
                    foreach ($answersChoose as $key => $ansVal) {
                        if ($ansVal['answer'] == 1) {
                            $correct .= "✅ <span class='text-success'>".$ansVal['a_t'] . ")</span><br>";
                        }
                        if ($ansVal['answer'] == 0) {
                            $correct .= "❌ <span class='text-danger'>".$ansVal['a_t'] . ")</span> &nbsp;<span class='text-success'>To'g'ri javob: ".$ansVal['true_ans'].")</span><br>";
                        }
                    }
                }
                $solvedRating .= "   ✅ <span class='text-success'>".$value['correctAnswers']."ta</span>   ❌ <span class='text-danger'>".$value['inCorrectAnswers']."ta</span>"."<br> $correct <br> ";
                $correct = "";
            $uncorrect = "";
            }
            // print_r($correct);
            print_r($solvedRating);
        };
    ?>

<script>
    let web_url = window.location.href;
    document.querySelector("p").innerHTML = "<span style='font-size:6px;'>" + web_url + "</span>";
    if(window.Telegram){
        const telegram = window.Telegram.WebApp
        const telegramData = telegram.initDataUnsafe
        if (Object.keys(telegramData).length === 0 || typeof telegramData.user === 'undefined') {
            document.querySelector("body").innerText =
                "404 not found";
                console.log(4354);
        } else {
            telegram.expand()
            // document.querySelector("#logbox").innerText = JSON.stringify(telegram, null, 4)
        }
        const themeParams = telegram.themeParams
        const mainButton = telegram.MainButton
    }else{
        alert('Bot orqali webapp da koring');
    }
        
</script>
</body>
</html>