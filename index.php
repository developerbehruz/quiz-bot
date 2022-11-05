<?php
    require('./config/config.php');
    $db = new dbmysqli;
    $db->dbConnect();
    date_default_timezone_set('Asia/Tashkent');
    define('API_KEY', "5777760851:AAFo9vCAXiUqW6Hw1MQXti3C60-SrUw0vUs");
    require './helpers/functions.php';
    $admins = json_decode(file_get_contents('config/json/admins.json'));
    $update = json_decode(file_get_contents('php://input'));
    file_put_contents("log.json", file_get_contents('php://input'));
    if (!is_null($update)) {
        if (!is_null($update->message)) {
            $message = $update->message;
            $chat_id = $message->chat->id;
            $type = $message->chat->type; 
            $miid =$message->message_id;
            $name = $message->from->first_name;
            $lname = $message->from->last_name;
            $full_name = $name . " " . $lname;
            $full_name = html($full_name);
            $user = $message->from->username ?? '';
            $fromid = $message->from->id;
            $text = html($message->text);
            $title = $message->chat->title;
            $chatuser = $message->chat->username;
            $chatuser = $chatuser ? $chatuser : "Shaxsiy Guruh!";
            $caption = $message->caption;
            $entities = $message->entities;
            $entities = $entities[0];
            $left_chat_member = $message->left_chat_member;
            $new_chat_member = $message->new_chat_member;
            $photo = $message->photo;
            $video = $message->video;
            $audio = $message->audio;
            $voice = $message->voice;
            $reply = $message->reply_markup;
            $fchat_id = $message->forward_from_chat->id;
            $fid = $message->forward_from_message_id;
        }else if(!is_null($update->callback_query)){
            $callback = $update->callback_query;
            $qid = $callback->id;
            $mes = $callback->message;
            $mid = $mes->message_id;
            $cmtx = $mes->text;
            $cid = $callback->message->chat->id;
            $ctype = $callback->message->chat->type;
            $cb_date = $callback->message->date;
            $cbid = $callback->from->id;
            $cbname = $callback->from->first_name;
            $cbuser = $callback->from->username;
            $data = $callback->data;
        }
    }

    $admin_main_keys = json_encode([
        'resize_keyboard' => true,
        'keyboard' => [
            [['text' => "Yaratilgan testlar 📝"]]
        ]
    ]);
    $user_main_keys = json_encode([
        'resize_keyboard' => true,
        'keyboard' => [
            [['text' => "Mavjud testlar 📝"]]
        ]
    ]);
    $user_question_keys = json_encode([
        'inline_keyboard' => [
            [['text' => "A", 'callback_data' => "answer_A"], ['text' => "B", 'callback_data' => "answer_B"]],
            [['text' => "C", 'callback_data' => "answer_C"], ['text' => "D", 'callback_data' => "answer_D"]]
        ]
    ]);

    if (!is_null($update)) {
        if (!is_null($update->message)) {
            // $user_lang = lang($fromid);
            if ($type == 'private') {
                if (channel($fromid)) {
                    if (in_array($fromid, $admins)) { 
                        if ($text == "/start") {
                            bot('sendMessage', [
                                'chat_id' => $fromid,
                                'text' => "Salom admin!1",
                                'reply_markup' => $admin_main_keys
                            ]); 
                        }
                        if ($text == "Yaratilgan testlar 📝") {
                            file_put_contents("step/quiz.json", json_encode(array("count" => 0,'prev' => 0,'next' => 30)));
                            $quiz_file = json_decode(file_get_contents("step/quiz.json"));
                            $quizs = $db->selectWhere('quiz',[
                                [
                                    'id' => $quiz_file->prev,
                                    'cn' => '>'
                                ],
                            ], " AND id <= $quiz_file->next");
                            $quiz_title = "<b>Yaratilgan testlar:</b>\n\n";
                            $i = $quiz_file->count;
                            foreach ($quizs as $key => $value) {
                                $i++;
                                $quiz_title .= "<b>$i</b>".". ".$value['quiz_title']."\n";
                                $keyy[] = ['text'=>$i, 'callback_data'=> 'quiz_' . $value['id']];
                                file_put_contents("step/quiz.json", json_encode(array("count" => $i,'prev' => $quiz_file->prev,'next' => $quiz_file->next)));
                                if ($i == 30) {
                                    break;
                                }

                            }
                            if ($quiz_file->prev != 0) {
                                $keyy[] = ['text'=>"⬅️ Orqaga", 'callback_data'=> "prev"];
                            }
                            $quizLatId = $db->selectWhere('quiz',[
                                [
                                    'id' => 0,
                                    'cn' => '>'
                                ],
                            ], " ORDER BY id DESC");
                            $quizLat = mysqli_fetch_assoc($quizLatId);
                            if ($quizLat['id'] >= $quiz_file->next) {
                                $keyy[] = ['text'=>"Oldinga ➡️", 'callback_data'=> "next"];
                            }
                            $keys = array_chunk($keyy, 4);
                            bot('sendMessage', [
                                'chat_id' => $fromid,
                                'text' => $quiz_title,
                                'parse_mode' => "HTML",
                                'reply_markup' => json_encode([
                                    'inline_keyboard' => $keys
                                ]),
                            ]);
                        }
                    }else{
                        if ($text == "/start") {
                            $myUser = myUser(['fromid','name','user','chat_type','lang','del'],[$fromid,$full_name,$user,'private','',0]);
                            bot('sendMessage', [
                                'chat_id' => $fromid,
                                'text' => "Assalomu aleykum",
                                'reply_markup' => $user_main_keys
                            ]);
                        }
                        if ($text == "Mavjud testlar 📝") {
                        	// bot('sendMessage', [
                        	// 	'chat_id' => $fromid,
                        	// 	'text' => "user"
                        	// ]);
                            file_put_contents("step/quiz_$fromid.json", json_encode(array("count" => 0,'prev' => 0,'next' => 30)));
                            $quiz_file = json_decode(file_get_contents("step/quiz_$fromid.json"));
                            $quizs = $db->selectWhere('quiz',[
                                [
                                    'id' => $quiz_file->prev,
                                    'cn' => '>'
                                ],
                            ], " AND id <= $quiz_file->next");
                            $quiz_title = "<b>Yaratilgan testlar:</b>\n\n";
                            $i = $quiz_file->count;
                            foreach ($quizs as $key => $value) {
                                $i++;
                                $quiz_title .= "<b>$i</b>".". ".$value['quiz_title']."\n";
                                $keyy[] = ['text'=>$i, 'callback_data'=> 'quizUser_' . $value['id']];
                                file_put_contents("step/quiz_$fromid.json", json_encode(array("count" => $i,'prev' => $quiz_file->prev,'next' => $quiz_file->next)));
                                if ($i == 30) {
                                    break;
                                }

                            }
                            if ($quiz_file->prev != 0) {
                                $keyy[] = ['text'=>"⬅️ Orqaga", 'callback_data'=> "prevUser"];
                            }
                            $quizLatId = $db->selectWhere('quiz',[
                                [
                                    'id' => 0,
                                    'cn' => '>'
                                ],
                            ], " ORDER BY id DESC");
                            $quizLat = mysqli_fetch_assoc($quizLatId);
                            if ($quizLat['id'] >= $quiz_file->next) {
                                $keyy[] = ['text'=>"Oldinga ➡️", 'callback_data'=> "nextUser"];
                            }
                            $keys = array_chunk($keyy, 4);
                            bot('sendMessage', [
                                'chat_id' => $fromid,
                                'text' => $quiz_title,
                                'parse_mode' => "HTML",
                                'reply_markup' => json_encode([
                                    'inline_keyboard' => $keys
                                ]),
                            ]);
                        }
                    }
                }
            }else{
                if ($text == "/start") {
                    myUser(['fromid','name','user','chat_type','lang','del'],[$chat_id,$title,$chatuser,'group','',0]);
                }
            }
        }else if(!is_null($update->callback_query)){
            if (channel($cbid)) {
                if ($ctype == 'private') {
                    // $user_lang = lang($cbid);
                    if ($data == 'res') {
                        bot('deleteMessage',[
                            'chat_id'=>$cbid,
                            'message_id'=>$mid,
                        ]);
                        bot('sendMessage',[
                            'chat_id'=>$cbid,
                            'text'=>"Assalomu aleykum!"
                        ]);
                    }
                    if ($data == "next") {
                        $quiz_file = json_decode(file_get_contents("step/quiz.json"));
                        $count = $quiz_file->count;
                        $prev = $quiz_file->prev + 30;
                        $next = $quiz_file->next + 30;
                        file_put_contents("step/quiz.json", json_encode(array("count" => $count,'prev' => $prev,'next' => $next)));
                        $quiz_file = json_decode(file_get_contents("step/quiz.json"));

                        $quizs = $db->selectWhere('quiz',[
                            [
                                'id' => $prev,
                                'cn' => '>'
                            ],
                        ], " AND id <= $next");

                        $quiz_title = "<b>Yaratilgan testlar:</b>\n\n";
                        $i = $count;
                        foreach ($quizs as $key => $value) {
                            $i++;
                            $quiz_title .= "<b>$i</b>".". ".$value['quiz_title']."\n";
                            $keyy[] = ['text'=>$i, 'callback_data'=> 'quiz_' . $value['id']];
                            // if ($i == 10) {
                            //     break;
                            // }
                        }
                        file_put_contents("step/quiz.json", json_encode(array("count" => $count + 30,'prev' => $prev,'next' => $next)));
                        
                        if ($quiz_file->prev != 0) {
                            $keyy[] = ['text'=>"⬅️ Orqaga", 'callback_data'=> "prev"];
                        }
                        $quizLatId = $db->selectWhere('quiz',[
                            [
                                'id' => 0,
                                'cn' => '>'
                            ],
                        ], " ORDER BY id DESC");
                        $quizLat = mysqli_fetch_assoc($quizLatId);
                        if ($quizLat['id'] >= $quiz_file->next) {
                            $keyy[] = ['text'=>"Oldinga ➡️", 'callback_data'=> "next"];
                        }
                        $keys = array_chunk($keyy, 5);
                        bot('editMessageText', [
                            'chat_id' => $cbid,
                            'text' => $quiz_title,
                            'message_id' => $mid, 
                            'parse_mode' => "HTML",
                            'reply_markup' => json_encode([
                                'inline_keyboard' => $keys
                            ]),
                        ]);
                    }
                    if ($data == "prev") {
                        $quiz_file = json_decode(file_get_contents("step/quiz.json"));
                        $count = $quiz_file->count - 60;
                        $prev = $quiz_file->prev - 30;
                        $next = $quiz_file->next - 30;
                        file_put_contents("step/quiz.json", json_encode(array("count" => $count,'prev' => $prev,'next' => $next)));
                        $quiz_file = json_decode(file_get_contents("step/quiz.json"));

                        $quizs = $db->selectWhere('quiz',[
                            [
                                'id' => $prev,
                                'cn' => '>'
                            ],
                        ], " AND id <= $next");

                        $quiz_title = "<b>Yaratilgan testlar:</b>\n\n";
                        $i = $count;
                        foreach ($quizs as $key => $value) {
                            $i++;
                            $quiz_title .= "<b>$i</b>".". ".$value['quiz_title']."\n";
                            $keyy[] = ['text'=>$i, 'callback_data'=> 'quiz_' . $value['id']];
                            // if ($i == 10) {
                            //     break;
                            // }
                        }
                        file_put_contents("step/quiz.json", json_encode(array("count" => $count + 30,'prev' => $prev,'next' => $next)));
                        
                        if ($quiz_file->prev != 0) {
                            $keyy[] = ['text'=>"⬅️ Orqaga", 'callback_data'=> "prev"];
                        }
                        $quizLatId = $db->selectWhere('quiz',[
                            [
                                'id' => 0,
                                'cn' => '>'
                            ],
                        ], " ORDER BY id DESC");
                        $quizLat = mysqli_fetch_assoc($quizLatId);
                        if ($quizLat['id'] >= $quiz_file->next) {
                            $keyy[] = ['text'=>"Oldinga ➡️", 'callback_data'=> "next"];
                        }
                        $keys = array_chunk($keyy, 5);
                        bot('editMessageText', [
                            'chat_id' => $cbid,
                            'text' => $quiz_title,
                            'message_id' => $mid, 
                            'parse_mode' => "HTML",
                            'reply_markup' => json_encode([
                                'inline_keyboard' => $keys
                            ]),
                        ]);
                    }
                    if (mb_stripos($data, "quiz_") !== false) {
                        $exp = explode("quiz_", $data);
                        $quiz_id = $exp[1];
                        bot('editMessageText', [
                            'chat_id' => $cbid,
                            'message_id' => $mid,
                            'text' => "Tanlang!",
                            'reply_markup' => json_encode([
                                'inline_keyboard' => [
                                    [['text' => "Kanalga tashlash", 'switch_inline_query' => "send"], ['text' => "Natijalarni tashlash", 'switch_inline_query' => "result_".$quiz_id]],
                                    [[ 'text' => "Natijalarni ko'rish", 'callback_data' => "view_res_".$quiz_id], ['text' => "Testni o'chirish", 'callback_data' => "del_quiz_$quiz_id"]]
                                ]
                            ])
                        ]);
                        file_put_contents("step/inline_quiz.tmp", $quiz_id);
                    }
                    if (mb_stripos($data, "view_res_") !== false) {
                        $exp = explode("view_res_", $data);
                        $quiz_id = $exp[1];
                        $result = $db->selectWhere('answers',[
                            [
                                'id' => 0,
                                'cn' => '>'
                            ],
                            [
                                'quiz_id' => $quiz_id,
                                'cn' => '='
                            ]
                        ]);
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
                                $getUsers = $db->selectWhere('quiz_users',[
                                    [
                                        'fromid' => trim($key),
                                        'cn' => '='
                                    ],
                                ]);
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
                        }else{
                            bot('sendMessage', [
                                'chat_id' => $cbid,
                                'text' => "Hali hech kim bu testni yechmadi!"
                            ]);
                        }
                        $quiz = $db->selectWhere('quiz', [
                            [
                                'id' => $quiz_id,
                                'cn' => '='
                            ],
                        ]);
                        if ($quiz->num_rows) {
                            $quiz_data = mysqli_fetch_assoc($quiz);
                            $solvedRating = "<b>Test nomi:</b> $quiz_data[quiz_title]\n<b>Test tavsifi: </b>$quiz_data[quiz_desc]\n\n";
                            $i = 0;
                            foreach ($finalRating as $key => $value) {
                                $i++;
                                foreach ($value['user'] as $userKey => $userVal) {
                                    $solvedRating .= "$i) <a href='tg://user?id=".$userVal['fromid']."'>".$userVal['name']."</a>";
                                    $keyy[] = ['text' => $i, 'callback_data' => "user_res_".$userVal['fromid']."_".$quiz_id];
                                }
                                $solvedRating .= "   ✅ ".$value['correctAnswers']."ta   ❌ ".$value['inCorrectAnswers']."ta\n";
                                if ($i == 20) {
                                    break;
                                }
                            }
                            if ($i >= 20) {
                                $keyy[] = [ 'text' => "Hammasi", 'web_app' => ['url' => 'https://u7428.xvest3.ru/tg_bots/quiz_bot/result_app/'.$quiz_id]];
                            }
                            $keys = array_chunk($keyy, 4);
                            bot('editMessageText', [
                                'chat_id' => $cbid,
                                'message_id' => $mid,
                                'text' => $solvedRating,
                                'parse_mode' => "HTML",
                                'reply_markup' => json_encode([
                                    'inline_keyboard' => $keys
                                ])
                            ]);
                        };
                    }
                    if (mb_strpos($data, "del_quiz_") !== false) {
                        $exp = explode("del_quiz_", $data);
                        $db->delete('quiz',[
                            [
                                'id' => $exp[1],
                                'cn' => '='
                            ],
                        ]);
                        $db->delete('questions',[
                            [
                                'quiz_id' => $exp[1],
                                'cn' => '='
                            ],
                        ]);
                        bot('editMessageText', [
                            'chat_id' => $cbid,
                            'message_id' => $mid,
                            'text' => "✅ Test muvoffaqiyatli o'chirildi!"
                        ]);
                    }
                    if ($data == "nextUser") {
                        $quiz_file = json_decode(file_get_contents("step/quiz_$cbid.json"));
                        $count = $quiz_file->count;
                        $prev = $quiz_file->prev + 30;
                        $next = $quiz_file->next + 30;
                        file_put_contents("step/quiz_$cbid.json", json_encode(array("count" => $count,'prev' => $prev,'next' => $next)));
                        $quiz_file = json_decode(file_get_contents("step/quiz_$cbid.json"));

                        $quizs = $db->selectWhere('quiz',[
                            [
                                'id' => $prev,
                                'cn' => '>'
                            ],
                        ], " AND id <= $next");

                        $quiz_title = "<b>Yaratilgan testlar:</b>\n\n";
                        $i = $count;
                        foreach ($quizs as $key => $value) {
                            $i++;
                            $quiz_title .= "<b>$i</b>".". ".$value['quiz_title']."\n";
                            $keyy[] = ['text'=>$i, 'callback_data'=> 'quizUser_' . $value['id']];
                            // if ($i == 10) {
                            //     break;
                            // }
                        }
                        file_put_contents("step/quiz_$cbid.json", json_encode(array("count" => $count + 30,'prev' => $prev,'next' => $next)));
                        
                        if ($quiz_file->prev != 0) {
                            $keyy[] = ['text'=>"⬅️ Orqaga", 'callback_data'=> "prevUser"];
                        }
                        $quizLatId = $db->selectWhere('quiz',[
                            [
                                'id' => 0,
                                'cn' => '>'
                            ],
                        ], " ORDER BY id DESC");
                        $quizLat = mysqli_fetch_assoc($quizLatId);
                        if ($quizLat['id'] >= $quiz_file->next) {
                            $keyy[] = ['text'=>"Oldinga ➡️", 'callback_data'=> "nextUser"];
                        }
                        $keys = array_chunk($keyy, 5);
                        bot('editMessageText', [
                            'chat_id' => $cbid,
                            'text' => $quiz_title,
                            'message_id' => $mid, 
                            'parse_mode' => "HTML",
                            'reply_markup' => json_encode([
                                'inline_keyboard' => $keys
                            ]),
                        ]);
                    }
                    if ($data == "prevUser") {
                        $quiz_file = json_decode(file_get_contents("step/quiz_$cbid.json"));
                        $count = $quiz_file->count - 60;
                        $prev = $quiz_file->prev - 30;
                        $next = $quiz_file->next - 30;
                        file_put_contents("step/quiz_$cbid.json", json_encode(array("count" => $count,'prev' => $prev,'next' => $next)));
                        $quiz_file = json_decode(file_get_contents("step/quiz_$cbid.json"));

                        $quizs = $db->selectWhere('quiz',[
                            [
                                'id' => $prev,
                                'cn' => '>'
                            ],
                        ], " AND id <= $next");

                        $quiz_title = "<b>Yaratilgan testlar:</b>\n\n";
                        $i = $count;
                        foreach ($quizs as $key => $value) {
                            $i++;
                            $quiz_title .= "<b>$i</b>".". ".$value['quiz_title']."\n";
                            $keyy[] = ['text'=>$i, 'callback_data'=> 'quizUser_' . $value['id']];
                            // if ($i == 10) {
                            //     break;
                            // }
                        }
                        file_put_contents("step/quiz_$cbid.json", json_encode(array("count" => $count + 30,'prev' => $prev,'next' => $next)));
                        
                        if ($quiz_file->prev != 0) {
                            $keyy[] = ['text'=>"⬅️ Orqaga", 'callback_data'=> "prevUser"];
                        }
                        $quizLatId = $db->selectWhere('quiz',[
                            [
                                'id' => 0,
                                'cn' => '>'
                            ],
                        ], " ORDER BY id DESC");
                        $quizLat = mysqli_fetch_assoc($quizLatId);
                        if ($quizLat['id'] >= $quiz_file->next) {
                            $keyy[] = ['text'=>"Oldinga ➡️", 'callback_data'=> "nextUser"];
                        }
                        $keys = array_chunk($keyy, 5);
                        bot('editMessageText', [
                            'chat_id' => $cbid,
                            'text' => $quiz_title,
                            'message_id' => $mid, 
                            'parse_mode' => "HTML",
                            'reply_markup' => json_encode([
                                'inline_keyboard' => $keys
                            ]),
                        ]);
                    }
                    if (mb_stripos($data, "quizUser_") !== false) {
                        $exp = explode("quizUser_", $data);
                        $quiz_id = $exp[1];
                        $quiz = $db->selectWhere('quiz', [
                            [
                                'id' => $quiz_id,
                                'cn' => '='
                            ],
                        ]);
                        if ($quiz->num_rows) {
                            $quiz_data = mysqli_fetch_assoc($quiz);
                            if ($quiz_data['quiz_time'] > strtotime("now")) {
                                $questions = $db->selectWhere('questions', [
                                    [
                                        'quiz_id' => $quiz_id,
                                        'cn' => '='
                                    ],
                                ]);
                                $question_count = 0;
                                foreach($questions as $key => $value) {
                                    $question_count += 1;
                                }
                                bot('editMessageText', [
                                    'chat_id' => $cbid,
                                    'message_id' => $mid,
                                    'text' => "<b>🔷 Test nomi:</b> $quiz_data[quiz_title]\n\n<b>🔷 Test tavsifi: </b>$quiz_data[quiz_desc]\n\n<b>🔷 Testlar soni:</b> $question_count"."ta",
                                    'parse_mode' => "HTML",
                                    'reply_markup' => json_encode([
                                        // 'resize_keyboard' => true,
                                        'inline_keyboard' => [
                                            [['text' => "🚩 Testni boshlash", 'callback_data' => "test_start_".$quiz_id]]
                                        ]
                                    ])
                                ]);
                            }else{
                                $questions = $db->selectWhere('questions', [
                                    [
                                        'quiz_id' => $quiz_id,
                                        'cn' => '='
                                    ],
                                ]);
                                $question_count = 0;
                                foreach($questions as $key => $value) {
                                    $question_count += 1;
                                }
                                bot('editMessageText', [
                                    'chat_id' => $cbid,
                                    'message_id' => $mid,
                                    'text' => "<b>🔷 Test nomi:</b> $quiz_data[quiz_title]\n\n<b>🔷 Test tavsifi: </b>$quiz_data[quiz_desc]\n\n<b>🔷 Testlar soni:</b> $question_count"."ta\n\n❗️ Bu test uchun berilgan vaqt tugagan!",
                                    'parse_mode' => "HTML",
                                ]);
                            }
                        };
                    }
                    if (mb_stripos($data, "test_start_") !== false) {
                        $exp = explode("test_start_", $data);
                        $quiz_id = $exp[1];
                        file_put_contents("step/ans_count_$cbid.json", json_encode(array("quiz_id" => $quiz_id, "count" => 1, "index" => 0)));
                        $question_index = json_decode(file_get_contents("step/ans_count_$cbid.json"));
                        $answers = $db->selectWhere('answers', [
                                [
                                    'fromid' => $cbid,
                                    'cn' => '='
                                ],
                        ], " AND quiz_id = $question_index->quiz_id");
                        if ($answers->num_rows) {
                            $del = $db->delete('answers',[
                                [
                                    'fromid' => $cbid,
                                    'cn' => '='
                                ],
                            ], " AND quiz_id = $question_index->quiz_id");
                        }

                        $questions = $db->selectWhere('questions', [
                            [
                                'quiz_id' => $quiz_id,
                                'cn' => '='
                            ],
                        ]);
                        foreach ($questions as $key => $value) {
                            $questionsID[] = $value['id'];
                            file_put_contents("step/quiz_$quiz_id.json", json_encode(array($questionsID)));
                        }
                        $questions_id = json_decode(file_get_contents("step/quiz_$quiz_id.json"));
                        $first_question = $db->selectWhere('questions', [
                            [
                                'id' => $questions_id[0][0],
                                'cn' => '='
                            ],
                        ], " AND quiz_id = $quiz_id");
                        if ($first_question->num_rows) {
                            $first_questionData = mysqli_fetch_assoc($first_question);
                            if (strlen($first_questionData['question']) > 5) {
                                bot('deleteMessage', [
                                    'chat_id' => $cbid,
                                    'message_id' => $mid
                                ]);
                                bot('sendPhoto', [
                                    'chat_id' => $cbid,
                                    // 'message_id' => $mid,
                                    'photo' => $first_questionData['question'],
                                    'caption' => "⁉️ 1-savol",
                                    'reply_markup' => $user_question_keys
                                ]);
                            }else{
                                bot('deleteMessage', [
                                    'chat_id' => $cbid,
                                    'message_id' => $mid
                                ]);
                                bot('sendMessage', [
                                    'chat_id' => $cbid,
                                    'text' => "⁉️ 1-savol\n\n".$first_questionData['question_text'],
                                    'reply_markup' => $user_question_keys
                                ]);
                            }
                            file_put_contents("step/ans_count_$cbid.json", json_encode(array("quiz_id" => $quiz_id, "count" => 1, "index" => 0)));
                        }
                    }
                    if (mb_stripos($data, "answer_") !== false) {
                        $question_index = json_decode(file_get_contents("step/ans_count_$cbid.json"));
                        $questions_id = json_decode(file_get_contents("step/quiz_$question_index->quiz_id.json"));
                        $exp = explode("answer_", $data);
                        $answer = $db->selectWhere('questions',[
                            [
                                'id' => $questions_id[0][$question_index->index],
                                'cn' => '='
                            ],
                            [
                                'quiz_id' => $question_index->quiz_id,
                                'cn' => '='
                            ],
                            [
                                't' => $exp[1],
                                'cn' => '='
                            ],
                        ]);
                        $true_ans = $db->selectWhere('questions',[
                            [
                                'id' => $questions_id[0][$question_index->index],
                                'cn' => '='
                            ],
                            [
                                'quiz_id' => $question_index->quiz_id,
                                'cn' => '='
                            ],
                        ]);

                        $true_ans_data = mysqli_fetch_assoc($true_ans);

                        $answ = mysqli_num_rows($answer) <=> 0;
                        $db->insertInto('answers',[
                            'fromid' => $cbid,
                            'quiz_id' => $question_index->quiz_id,
                            'question_id' => $questions_id[0][$question_index->index],
                            'answer' => $answ,
                            'a_t' => $exp[1],
                            'true_ans' => $true_ans_data['t']
                        ]);
                        file_put_contents("step/ans_count_$cbid.json", json_encode(array("quiz_id" => $question_index->quiz_id, "count" => $question_index->count + 1, "index" => $question_index->index + 1)));
                        $question_index = json_decode(file_get_contents("step/ans_count_$cbid.json"));

                        $questions = $db->selectWhere('questions', [
                            [
                                'id' => $questions_id[0][$question_index->index],
                                'cn' => '='
                            ],
                        ], " AND quiz_id = $question_index->quiz_id");
                        if ($questions->num_rows) {
                            $questionsData = mysqli_fetch_assoc($questions);
                            if (strlen($questionsData['question']) > 5) {
                                bot('deleteMessage', [
                                    'chat_id' => $cbid,
                                    'message_id' => $mid,
                                ]);
                                bot('sendPhoto', [
                                    'chat_id' => $cbid,
                                    'photo' => $questionsData['question'],
                                    'caption' => "⁉️ ".$question_index->count.'-savol',
                                    'reply_markup' => $user_question_keys
                                ]);
                            }else{
                                bot('deleteMessage', [
                                    'chat_id' => $cbid,
                                    'message_id' => $mid,
                                ]);
                                bot('sendMessage', [
                                    'chat_id' => $cbid,
                                    'text' => "⁉️ ".$question_index->count."-savol\n\n".$questionsData['question_text'],
                                    'reply_markup' => $user_question_keys
                                ]);
                            }
                        }else{
                            $question_index = json_decode(file_get_contents("step/ans_count_$cbid.json"));

                            $questions = $db->selectWhere('answers', [
                                [
                                    'fromid' => $cbid,
                                    'cn' => '='
                                ],
                            ], " AND quiz_id = $question_index->quiz_id");
                            bot('deleteMessage', [
                                'chat_id' => $cbid,
                                'message_id' => $mid,
                            ]);
                            $unCorrect = 0;
                            $correct = 0;
                            $user_selected = "";
                            $i = 0;
                            foreach ($questions as $key => $value) {
                                $i++;
                                if ($value['answer'] == "0") {
                                    $unCorrect += 1;
                                    $user_selected .= "<b>$i: </b>❌ Noto'g'ri\n";
                                }
                                if ($value['answer'] == "1") { 
                                    $correct += 1;
                                    $user_selected .= "<b>$i: </b>✅ To'g'ri\n";
                                }
                            }
                            bot('sendMessage', [
                                'chat_id' => $cbid,
                                'text' => "$user_selected\n<b>✅ To'g'ri javoblar:</b> $correct"."ta\n\n<b>❌ Noto'g'ri javoblar:</b> $unCorrect"."ta",
                                'parse_mode' => "HTML"
                            ]);
							array_map('unlink', glob("step/ans_count_$cbid.*"));
							array_map('unlink', glob("step/quiz_$cbid.*"));
                        }
                    }
                    if (mb_strpos($data, "user_res_") !== false) {
                        $exp = explode("user_res_", $data);
                        $exp = explode("_", $exp[1]);
                        $user_id = $exp[0];
                        $quiz_id = $exp[1];
                        $questions = $db->selectWhere('answers', [
                            [
                                'fromid' => $user_id,
                                'cn' => '='
                            ],
                        ], " AND quiz_id = $quiz_id");
                        $get_user = $db->selectWhere('quiz_users', [
                            [
                                'fromid' => $user_id,
                                'cn' => '='
                            ],
                        ]);
                        $user_data = mysqli_fetch_assoc($get_user);
                        $unCorrect = 0;
                        $correct = 0;
                        $user_selected = "<a href='tg://user?id=".$user_data['fromid']."'>".$user_data['name']."</a> (@".$user_data['user'].")\n\n";
                        $i = 0;
                        foreach ($questions as $key => $value) {
                            $i++;
                            if ($value['answer'] == "0") {
                                $unCorrect += 1;
                                $user_selected .= "<b>$i: </b>❌".$value['a_t'].").  To’g’ri javob: <b>".$value['true_ans'].")</b>\n";
                            }
                            if ($value['answer'] == "1") {
                                $correct += 1;
                                $user_selected .= "<b>$i: </b>✅".$value['a_t']."\n";
                            }
                        }
                        bot('sendMessage', [
                            'chat_id' => $cbid,
                            'text' => "$user_selected\n<b>✅ To'g'ri javoblar:</b> $correct"."ta\n\n<b>❌ Noto'g'ri javoblar:</b> $unCorrect"."ta",
                            'parse_mode' => "HTML"
                        ]);
                    } 
                }
                if ($ctype == "channel") {
                    if (mb_stripos($data, "channel_answer_") !== false) {
                        // $quiz_id = file_get_contents("step/inline_quiz.tmp");
                        // if (mb_stripos($quiz_id, "user_") !== false) {
                        //     $expid = explode("user_", $quiz_id);
                        //     $quiz_id = $expid[1];
                        // } 
                        $exp = explode("channel_answer_", $data);
                        $exp = explode("_", $exp[1]);
                        $ans = $exp[0];
                        $quest_id = $exp[1];

                        $getQuizId = $db->selectWhere('questions', [
                            [
                                'id' => $quest_id,
                                'cn' => "="
                            ]
                        ]);
                        $getQuizIdFetch = mysqli_fetch_assoc($getQuizId);
                        $quiz_id = $getQuizIdFetch['quiz_id'];
                        $quiz = $db->selectWhere('quiz', [
                            [
                                'id' => $quiz_id,
                                'cn' => '='
                            ],
                        ]);
                        if ($quiz->num_rows) {
                            $time = strtotime("now");
                            $quiz_data = mysqli_fetch_assoc($quiz);
                            
                            
                            if ($quiz_data['quiz_time'] > $time) {
                                $get_users = $db->selectWhere("quiz_users", [
                                    [
                                        'fromid' => $cbid,
                                        'cn' => "="
                                    ]
                                ]);
                                if ($get_users->num_rows) {
    
                                }else{
                                    // bot('sendMessage', [
                                    //     'chat_id' => 1130942146,
                                    //     'text' => "$cbid"
                                    // ]);
                                    $db->insertInto('quiz_users',[
                                        'fromid' => $cbid,
                                        'name' => $cbname,
                                        'user' => $cbuser,
                                        'chat_type' => "private",
                                        'del' => "0"
                                    ]);
                                }
    
                                // $text = "answer_A_12";
                                $answer = $db->selectWhere('questions',[
                                    [
                                        'id' => $quest_id,
                                        'cn' => '='
                                    ],
                                    [
                                        'quiz_id' => $quiz_id,
                                        'cn' => '='
                                    ],
                                    [
                                        't' => $ans,
                                        'cn' => '='
                                    ],
                                ]);
    
                                $getAnsw = $db->selectWhere('answers',[
                                    [
                                        'fromid' => $cbid,
                                        'cn' => '='
                                    ],
                                    [
                                        'quiz_id' => $quiz_id,
                                        'cn' => '='
                                    ],
                                    [
                                        'question_id' => $quest_id,
                                        'cn' => '='
                                    ],
                                ]);
                                if ($getAnsw->num_rows) {
                                    bot('answerCallbackQuery',[
                                        'callback_query_id' => $qid,
                                        'text' => "❗️ Bu testga javob berdingiz!",
                                        'show_alert' => true
                                    ]);
                                }else{
                                    $true_ans = $db->selectWhere('questions',[
                                        [
                                            'id' => $quest_id,
                                            'cn' => '='
                                        ],
                                        [
                                            'quiz_id' => $quiz_id,
                                            'cn' => '='
                                        ],
                                    ]);
            
                                    $true_ans_data = mysqli_fetch_assoc($true_ans);

                                    $answ = mysqli_num_rows($answer) <=> 0;
                                    $db->insertInto('answers',[
                                        'fromid' => $cbid,
                                        'quiz_id' => $quiz_id,
                                        'question_id' => $quest_id,
                                        'answer' => $answ,
                                        'a_t' => $ans,
                                        'true_ans' => $true_ans_data['t']
                                    ]);
    
                                    bot('answerCallbackQuery',[
                                        'callback_query_id' => $qid,
                                        'text' => "Javobingiz qabul qilindi ✅",
                                        'show_alert' => true
                                    ]);
                                }
                            }else{
                                // $get_users = $db->selectWhere("quiz_users", [
                                //     [
                                //         'fromid' => $cbid,
                                //         'cn' => "="
                                //     ]
                                // ]);
                                // if ($get_users->num_rows) {
    
                                // }else{
                                //     $db->insertInto('quiz_users',[
                                //         'fromid' => $cbid,
                                //         'name' => $cbname,
                                //         'user' => $cbuser,
                                //         'chat_type' => "private",
                                //         'del' => "0",
                                //         'a_t' => $exp[1],
                                //         'true_ans' => $true_ans_data['t']
                                //     ]);
                                // }
    
                                // $text = "answer_A_12";
                                $exp = explode("channel_answer_", $data);
                                $exp = explode("_", $exp[1]);
                                $ans = $exp[0];
                                $quest_id = $exp[1];
                                $answer = $db->selectWhere('questions',[
                                    [
                                        'id' => $quest_id,
                                        'cn' => '='
                                    ],
                                    [
                                        'quiz_id' => $quiz_id,
                                        'cn' => '='
                                    ],
                                    [
                                        't' => $ans,
                                        'cn' => '='
                                    ],
                                ]);
    
                                // $getAnsw = $db->selectWhere('answers',[
                                //     [
                                //         'fromid' => $cbid,
                                //         'cn' => '='
                                //     ],
                                //     [
                                //         'quiz_id' => $quiz_id,
                                //         'cn' => '='
                                //     ],
                                //     [
                                //         'question_id' => $quest_id,
                                //         'cn' => '='
                                //     ],
                                // ]);
                                // if ($getAnsw->num_rows) {
                                    // bot('answerCallbackQuery',[
                                    //     'callback_query_id' => $qid,
                                    //     'text' => "❗️ Bu testga javob berdingiz!",
                                    //     'show_alert' => true
                                    // ]);
                                // }else{
                                    $true_ans = $db->selectWhere('questions',[
                                        [
                                            'id' => $quest_id,
                                            'cn' => '='
                                        ],
                                        [
                                            'quiz_id' => $quiz_id,
                                            'cn' => '='
                                        ],
                                    ]);
            
                                    $true_ans_data = mysqli_fetch_assoc($true_ans);

                                    $answ = mysqli_num_rows($answer) <=> 0;
                                    // $db->insertInto('answers',[
                                    //     'fromid' => $cbid,
                                    //     'quiz_id' => $quiz_id,
                                    //     'question_id' => $quest_id,
                                    //     'answer' => $answ,
                                    //     'a_t' => $ans,
                                    //     'true_ans' => $true_ans_data['t']
                                    // ]);

                                    if ($ans == $true_ans_data['t']) {
                                        bot('answerCallbackQuery',[
                                            'callback_query_id' => $qid,
                                            'text' => "To'g'ri ✅",
                                            'show_alert' => true
                                        ]);
                                    }else {
                                        bot('answerCallbackQuery',[
                                            'callback_query_id' => $qid,
                                            'text' => "❌ Noto'g'ri ✅ To'g'ri javob: $true_ans_data[t]",
                                            'show_alert' => true
                                        ]);
                                    }
                                // }
                                // bot('answerCallbackQuery',[
                                //     'callback_query_id' => $qid,
                                //     'text' => "❗️ Bu test uchun berilgan vaqt tugagan.",
                                //     'show_alert' => true
                                // ]);
                            }
                        }
                    }
                }
            }
        }else if (!is_null($update->inline_query)) {
            $i_query = $update->inline_query;
            $query = $i_query->query;
            $quiz_id = file_get_contents("step/inline_quiz.tmp");
            if (mb_stripos($quiz_id, "user_") !== false) {
                $expid = explode("user_", $quiz_id);
                $quiz_id = $expid[1];
            }
            $quiz = $db->selectWhere('quiz',[
                [
                    'id' => $quiz_id,
                    'cn' => '='
                ],
            ]);
            $questions = $db->selectWhere('questions',[
                [
                    'quiz_id' => $quiz_id,
                    'cn' => '='
                ],
            ]);
            $quiz_data = mysqli_fetch_assoc($quiz);
            $question_count = 0;
            $time = date("Y-m-d H:i:s", $quiz_data['quiz_time']);
            foreach($questions as $key => $value) {
                $question_count += 1;
            }
            if ($query == "send") {
                $timeNow = strtotime("now");
                if ($quiz_data['quiz_time'] > $timeNow) {
                    bot('answerInlineQuery', [
                        'inline_query_id' => $i_query->id,
                        'cache_time' => 5,
                        // 'switch_pm_text' => "Yordam",
                        'switch_pm_parameter' => "BONUS_CODE-AB445__PRAM1-infomir",
                        // 'next_offset' => "5",
                        'results' => json_encode([
                            [
                                'type' => "article",
                                'id' => uniqid(),
                                'title' => "$quiz_data[quiz_title]",
                                'input_message_content' => [
                                    'message_text' => "🆕 Yangi test boshlandi!\n\n<b>🔷Test nomi:</b> $quiz_data[quiz_title]\n<b>🔷Test tavsifi: </b>$quiz_data[quiz_desc]\n<b>🔷Savollar: </b>$question_count"."ta\n<b>🔷Testning tugash vaqti:</b> $time",
                                    'parse_mode' => 'html'
                                ],
                                'description' => "$quiz_data[quiz_desc]",
                            ]
                        ]),
                    ]);
                }else{
                    bot('answerInlineQuery', [
                        'inline_query_id' => $i_query->id,
                        'cache_time' => 5,
                        // 'switch_pm_text' => "Yordam",
                        'switch_pm_parameter' => "BONUS_CODE-AB445__PRAM1-infomir",
                        // 'next_offset' => "5",
                        'results' => json_encode([
                            [
                                'type' => "article",
                                'id' => uniqid(),
                                'title' => "Bu testni kanalga tashlay olmaysiz!",
                                'input_message_content' => [
                                    'message_text' => "🆕 Yangi test boshlandi!\n\n<b>🔷Test nomi:</b> $quiz_data[quiz_title]\n<b>🔷Test tavsifi: </b>$quiz_data[quiz_desc]\n<b>🔷Savollar: </b>$question_count"."ta\n<b>🔷Testning tugash vaqti:</b> Test uchun berilgan vaqt tugagan",
                                    'parse_mode' => 'html'
                                ],
                                'description' => "Test uchun berilgan vaqt tugagan!",
                            ]
                        ]),
                    ]);
                }
            }
            if (mb_strpos($query, "result_") !== false) {
                $exp = explode("result_", $query);
                $quiz_id = $exp[1];
                $result = $db->selectWhere('answers',[
                    [
                        'id' => 0,
                        'cn' => '>'
                    ],
                    [
                        'quiz_id' => $quiz_id,
                        'cn' => '='
                    ]
                ]);
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
                        $getUsers = $db->selectWhere('quiz_users',[
                            [
                                'fromid' => trim($key),
                                'cn' => '='
                            ],
                        ]);
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
                $quiz = $db->selectWhere('quiz', [
                    [
                        'id' => $quiz_id,
                        'cn' => '='
                    ],
                ]);
                if ($quiz->num_rows) {
                    $quiz_data = mysqli_fetch_assoc($quiz);
                    $solvedRating = "";
                    $i = 1;
                    foreach ($finalRating as $key => $value) {
                        foreach ($value['user'] as $userKey => $userVal) {
                            $solvedRating .= "$i) "."<a href='tg://user?id=".$userVal['fromid']. "'>".$userVal['name']."</a>";
                        }
                        $solvedRating .= "   ✅ ".$value['correctAnswers']."ta   ❌ ".$value['inCorrectAnswers']."ta\n";
                        $i++;
                        if ($i == 21) {
                            break;
                        }
                    }
                    bot('answerInlineQuery', [
                        'inline_query_id' => $i_query->id,
                        'cache_time' => 5,
                        // 'switch_pm_text' => "Yordam",
                        'switch_pm_parameter' => "BONUS_CODE-AB445__PRAM1-infomir",
                        // 'next_offset' => "5",
                        'results' => json_encode([
                            [
                                'type' => "article",
                                'id' => uniqid(),
                                'title' => "Natijalar\n\n",
                                'input_message_content' => [
                                    'message_text' => "⏰ Test natijalari! (Top 20talik)\n\n<b>🔷Test nomi:</b> $quiz_data[quiz_title]\n<b>🔷Test tavsifi: </b>$quiz_data[quiz_desc]\n<b>🔷Savollar: </b>$question_count"."ta\n\n$solvedRating",
                                    'parse_mode' => 'html'
                                ],
                                'description' => "$quiz_data[quiz_title]\n\n$quiz_data[quiz_desc]",
                            ]
                        ]),
                    ]);  
                };
            }
        }else if (!is_null($update->channel_post)) {
            $channel_post = $update->channel_post;
            $chnl_mid = $channel_post->message_id;
            $chnl_id = $channel_post->sender_chat->id;
            $chnl_text = $channel_post->text;
            // bot('deleteMessage', [
            //     'chat_id' => $chnl_id,
            //     'message_id' => $chnl_mid,
            // ]);
            $quiz_id = file_get_contents("step/inline_quiz.tmp");
            if (mb_stripos($quiz_id, "user_") !== false) {
                $expid = explode("user_", $quiz_id);
                $quiz_id = $expid[1];
            }
            $getQuizTime = $db->selectWhere('quiz', [
                [
                    'id' => $quiz_id,
                    'cn' => "="
                ]
            ]);
            $quiz_data = mysqli_fetch_assoc($getQuizTime);
            $questions = $db->selectWhere('questions',[
                [
                    'quiz_id' => $quiz_id,
                    'cn' => '='
                ],
            ]);
            $timeNow = strtotime("now");
            if ($quiz_data['quiz_time'] > $time) {
                if (mb_stripos($chnl_text, "🆕 Yangi test boshlandi!") !== false AND mb_stripos($chnl_text, "🔷Test nomi:") !== false AND mb_stripos($chnl_text, "🔷Test tavsifi:")  !== false) {
                    $i = 0;
            		foreach ($questions as $key => $value) {
                        $i++;
                        if ($i == 21) {
                            file_put_contents("config/json/channel_post.json", json_encode(array("channel_id"=>$chnl_id, "quiz_id"=>$quiz_id, "question_id"=>$value['id'])));
                            break;
                        }
                        bot('sendMessage', [
                            'chat_id' => 1130942146,
                            'text' => $i
                        ]);
                        if (strlen($value['question']) > 5) {
                            bot('sendPhoto', [
                                'chat_id' => $chnl_id,
                                'photo' => $value['question'],
                                'caption' => $i."-savol",
                                'reply_markup' => json_encode([
                                    'inline_keyboard' => [
                                        [['text' => "A", 'callback_data' => "channel_answer_A_$value[id]"], ['text' => "B", 'callback_data' => "channel_answer_B_$value[id]"]],
                                        [['text' => "C", 'callback_data' => "channel_answer_C_$value[id]"], ['text' => "D", 'callback_data' => "channel_answer_D_$value[id]"]]
                                    ]
                                ])
                            ]);
                        }else{
                            $msg = bot('sendMessage', [
                                'chat_id' => $chnl_id,
                                'text' => "⁉️ ".$i."-savol\n\n".$value['question_text'],
                                'reply_markup' => json_encode([
                                    'inline_keyboard' => [
                                        [['text' => "A", 'callback_data' => "channel_answer_A_$value[id]"], ['text' => "B", 'callback_data' => "channel_answer_B_$value[id]"]],
                                        [['text' => "C", 'callback_data' => "channel_answer_C_$value[id]"], ['text' => "D", 'callback_data' => "channel_answer_D_$value[id]"]]
                                    ]
                                ])
                            ]);
                            bot('sendMessage', [
	                        	'chat_id' => 1130942146,
	                        	'text' => json_encode($msg)
	                        ]);
                        }
                    }
                }
            }
        }
    }
    include 'helpers/admin/admin.php';
    include 'helpers/sendMessage.php';
    include 'helpers/channel_post.php';
?>