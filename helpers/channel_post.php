<?php 
	if ($_REQUEST['sendQuestion']) {
		$quiz_data = json_decode(file_get_contents("config/json/channel_post.json"));
		if (!is_null($quiz_data->quiz_id)) {
			$questions = $db->selectWhere("questions", [
				[
					'quiz_id' => $quiz_data->quiz_id,
					'cn' => "="
				],
				[
					'id' => $quiz_data->question_id,
					'cn' => ">="
				],
			], " limit 20");
			if ($questions->num_rows) {
				$i = 20;
				$quiz_id = file_get_contents("step/inline_quiz.tmp");
				foreach ($questions as $key => $value) {
		            $i++;
		            if ($i == 41) {
		                file_put_contents("config/json/channel_post.json", json_encode(array("channel_id"=>$quiz_data->channel_id, "quiz_id"=>$quiz_id, "question_id"=>$value['id'])));
		                break;
		            }
		            bot('sendMessage', [
		                'chat_id' => 1130942146,
		                'text' => $i
		            ]);
		            if (strlen($value['question']) > 5) {
		                bot('sendPhoto', [
		                    'chat_id' => $quiz_data->channel_id,
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
		                    'chat_id' => $quiz_data->channel_id,
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
				file_put_contents('config/json/channel_post.json',''); 
			}else{
				file_put_contents('config/json/channel_post.json',''); 
			}
		}
	}
?>