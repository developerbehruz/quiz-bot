<?php
	date_default_timezone_set("Asia/Tashkent");
	if (!is_null($update)) {
		$admins = json_decode(trim(file_get_contents('config/json/admins.json')));
		$admin = json_decode(file_get_contents('helpers/admin/json/' . $fromid . '.json'));
		$home_keyboard = json_encode([
			'inline_keyboard'=>[
				[['text'=>"Admin qo'shish", 'callback_data'=>'add_admin'],['text'=>"Admin o'chirish", 'callback_data'=>'delete_admin'],],
				[['text'=>"Kanal sozlash",'callback_data'=>'setting_channel'],['text'=>'Reklama yuborish','callback_data'=>'send_ads'],],
				[['text'=>"Test qo'shish", 'callback_data'=>"add_quiz"]],
			],
		]);
		$calncel_add_admin = json_encode([
			'inline_keyboard'=>[
				[['text'=>"Bekor qilish", 'callback_data'=>'calncel_add_admin'],],
			],
		]);
		$cancel_home = json_encode([
			'inline_keyboard'=>[
				[['text'=>"Ortga", 'callback_data'=>'cancel_home'],],
			],
		]);
		$setting_channel = json_encode([
			'inline_keyboard'=>[
				[['text'=>"Kanal qo'shish", 'callback_data'=>'add_channel'],['text'=>"Kanal o'chirish", 'callback_data'=>'remove_channel'],],
				[['text'=>"Majburiy azolik On",'callback_data'=>'channel_on'],['text'=>'Majburiy azolik Off','callback_data'=>'channel_off'],],
			],
		]);
		
		$calncel_send_ads = json_encode([
			'inline_keyboard'=>[
				[['text'=>"Yuborish", 'callback_data'=>'confirm_send_ads'],['text'=>"Bekor qilish", 'callback_data'=>'calncel_send_ads'],],
			],
		]);

		$answer = json_decode(file_get_contents("step/answer_$fromid.json"));
		$quizBtns = json_encode([
			'inline_keyboard' => [
				[['text' => "A " . (($answer->a) ? "✅" : "❌"), 'callback_data' => "ans_a"],['text' => "B " . (($answer->b) ? "✅" : "❌"), 'callback_data' => "ans_b"],],
				[['text' => "C " . (($answer->c) ? "✅" : "❌"), 'callback_data' => "ans_c"],['text' => "D " . (($answer->d) ? "✅" : "❌"), 'callback_data' => "ans_d"],],
				[['text' => "Keyngisi ➡️", 'callback_data' => "addNextQuest"]]
			],
		]);
		if (!is_null($update->message)) {
		    if (in_array($fromid,$admins)) { 
		        if ($text == '/admin') {
		        	file_put_contents('helpers/admin/json/' . $fromid . '.json', '');
		            $users = $db->selectWhere('quiz_users',[
		                'id'=>0,
		                'cn'=>'>'
		            ]);
		            $only_users = 0;
		            $active_users = 0;
		            $only_groups = 0;
		            $active_groups = 0;
		            foreach ($users as $key => $value) {
		                if ($value['chat_type'] == 'private') {
		                    $only_users+=1;
		                    if ($value['del']=='0') {
		                        $active_users+=1;
		                    }
		                }else{
		                    $only_groups+=1;
		                    if ($value['del']=='0') {
		                        $active_groups+=1;
		                    }
		                }
		            }
		            bot('sendMessage',[
		                'chat_id'=>$fromid,
		                'text'=>"Bot statistikasi:\n\nGuruh va userlar: " . $users->num_rows  . "ta\nBarcha userlar: " . $only_users . "ta\nActive userlar: " . $active_users . "ta\nBarcha Guruhlar: " . $only_groups . "ta\nActive Guruhlar: " . $active_groups . "ta",
		                'reply_markup'=>$home_keyboard,
		            ]);
		        }else if(mb_stripos($text, '/del_admin_')!==false){
		        	$exp = explode('/del_admin_', $text);
		        	$del_admin_id = $exp[1];
		        	$before_admin = [];
		        	foreach ($admins as $key => $value) {
		        		($value==$del_admin_id) ? true : $before_admin[] = $value;
		        	}
		        	file_put_contents('config/json/admins.json', json_encode($before_admin));
		        	bot('sendMessage',[
		        		'chat_id'=>$fromid,
		        		'text'=>"Admin muoffaqiyatli o'chirildi.",
		        		'reply_markup'=>$home_keyboard
		        	]);
		        }else if(mb_stripos($text, '/del_channel_')!==false){
		        	$exp = explode('/del_channel_', $text);
		        	$del = $db->delete('quiz_channels',[
						[
							'id'=>trim($exp[1]),
							'cn'=>'='
						],
					]);
		        	bot('sendMessage',[
		        		'chat_id'=>$fromid,
		        		'text'=>"Kanal muoffaqiyatli o'chirildi.",
		        		'reply_markup'=>$home_keyboard
		        	]);
		        }else{
			    	$bool = true;
			    	if ($admin->menu == 'add_admin' && $admin->step == 0) {
			    		if (!is_null($update->message->forward_from)) {
			    			if (!is_null($update->message->forward_from->id)) {
			    				$bool = false;
			    				$bot_admins = json_decode(file_get_contents('config/json/admins.json'));
			    				$bot_admins[] = $update->message->forward_from->id;
			    				file_put_contents('config/json/admins.json',json_encode($bot_admins));
			    			}
			    		}
			    		if ($bool) {
			    			bot('sendMessage',[
								'chat_id'=>$fromid,
								'text'=>"Admin qo'shilmadi.\nYangi admin qo'shish uchun yangi admin habaridan forward yuboring.\n\nEslatma: Yangi admin sozlamarida uzatilgan habar hamma uchun yoniq bo'lish zarur yo'qsa yangi admin ID sini olishning imkoni bo'lmaydi!",
								'reply_markup'=>$calncel_add_admin
							]);
			    		}else{
			    			if ($update->message->forward_from->id == $fromid) {
			    				bot('sendMessage',[
									'chat_id'=>$fromid,
									'text'=>"Siz o'zingizni o'zingiz admin qila olmaysiz. Avvaldan adminsiz.",
									'reply_markup'=>$calncel_add_admin
								]);
			    			}else{
			    				bot('sendMessage',[
			    					'chat_id'=>$fromid,
			    					'text'=>"Admin muoffaqiyatli qo'shildi!",
			    					'reply_markup'=>$home_keyboard
			    				]);
			    				file_put_contents('helpers/admin/json/' . $fromid . '.json', '');
			    			}
			    		}
			    	}else if($admin->menu == 'add_channel' && $admin->step == 0){
			    		if (mb_stripos($text, "@")!==false) {
			    			$getchat = bot('getChat',[
			                    'chat_id'=>$text
			                ]);
			                $id = $getchat->result->id;
                			$title = $getchat->result->title;
                			$channels = $db->selectWhere('quiz_channels',[
								[
									'object'=>$id,
									'cn'=>'='
								],
							]);
                			if (!$channels->num_rows) {
                				$getchatadmin = bot('getChatMember',[
			                        'chat_id'=>$id,
			                        'user_id'=>$fromid
			                    ]);
			                    $status = $getchatadmin->result->status;
			                    if ($status == "administrator" or $status == "creator") {
			                    	if ($db->insertInto('quiz_channels',['name'=>'channel','object'=>$id])) {
			                    		bot('sendmessage',[
			                                'chat_id'=>$fromid,
			                                'text'=>"<b>Kanal sozlandi.</b>",
			                                'parse_mode'=>'html',
			                                'reply_markup'=>$home_keyboard
			                            ]);
			                            file_put_contents('helpers/admin/json/' . $fromid . '.json', '');
			                    	}else{
			                    		bot('sendmessage',[
			                                'chat_id'=>$fromid,
			                                'text'=>"<b>Kanal sozlandi.</b>",
			                                'parse_mode'=>'html',
			                                'reply_markup'=>$home_keyboard
			                            ]);
			                            file_put_contents('helpers/admin/json/' . $fromid . '.json', '');
			                    	}
			                    }else{
			                    	bot('sendmessage',[
		                                'chat_id'=>$fromid,
		                                'text'=>"<b>Bot yoki siz kanalda admin emassiz.</b>",
		                                'parse_mode'=>'html',
		                                'reply_markup'=>$home_keyboard
		                            ]);
		                            file_put_contents('helpers/admin/json/' . $fromid . '.json', '');
			                    }
                			}else{
                				bot('sendMessage',[
									'chat_id'=>$fromid,
									'text'=>"<b>Bot 🔴 " . $title . " kanaliga avvaldan ulangan!</b>",
									'parse_mode'=>'html',
									'reply_markup'=>$cancel_home
								]);
								file_put_contents('helpers/admin/json/' . $fromid . '.json', '');
                			}
			    		}else{
			    			bot('sendMessage',[
								'chat_id'=>$fromid,
								'text'=>"Kanal qo'shish uchun kanal usernameni yuboring.\nQuyidagi formatda @okdeveloper",
								'reply_markup'=>$cancel_home
							]);
			    		}
			    	}else if($admin->menu == 'send_ads' && $admin->step == 0){
			    		if (!is_null($update->message)) {
			    			if (!is_null($message->reply_markup)) {
				    			bot('copyMessage',[
				    				'chat_id'=>$fromid,
				    				'from_chat_id'=>$fromid,
				    				'message_id'=>$miid,
				    				'reply_markup'=>json_encode($message->reply_markup),
				    			]);
				    			bot('sendMessage',[
				    				'chat_id'=>$fromid,
				    				'text'=>"Yuborishlikka tayyormi?",
				    				'reply_to_message_id'=>$miid+1,
				    				'reply_markup'=>$calncel_send_ads
				    			]);
				    			file_put_contents('config/json/sendMessage.json', json_encode(array('from_chat_id' => $fromid, 'message_id' => $miid, 'reply_markup' => $update->reply_markup)));
			    			}else{
			    				bot('copyMessage',[
				    				'chat_id'=>$fromid,
				    				'from_chat_id'=>$fromid,
				    				'message_id'=>$miid,
				    			]);
				    			bot('sendMessage',[
				    				'chat_id'=>$fromid,
				    				'text'=>"Yuborishlikka tayyormi?",
				    				'reply_to_message_id'=>$miid+1,
				    				'reply_markup'=>$calncel_send_ads
				    			]);
				    			file_put_contents('config/json/sendMessage.json', json_encode(array('from_chat_id' => $fromid, 'message_id' => $miid)));
			    			}
			    		}
			    	}
			    }

				$quizStep = file_get_contents("step/addquiz_$fromid.tmp");
				if ($quizStep == "quiz_title") {
					bot('sendMessage', [
						'chat_id' => $fromid,
						'text' => "Yaxshi. Endi menga testingiz tavsifini yuboring. Bu ixtiyoriy, bu bosqichni tashlab ketishingiz mumkin: /skip."
					]);
					$db->insertInto('quiz', ['fromid' => "$fromid",'quiz_title' => "$text"]);
					file_put_contents("step/addquiz_$fromid.tmp", "quiz_desc");
				}else if ($quizStep == "quiz_desc") {
					if ($text == "/skip") {
						$quiz = $db->selectWhere('quiz',[
							'id' => 0,
							'cn' => '>'
						], " ORDER BY id DESC");
						if ($quiz->num_rows) {
							$quiz_data = mysqli_fetch_assoc($quiz);
							$db->update('quiz',[
								'quiz_desc' => "Kiritmagan!",
							],[
								'id' => "$quiz_data[id]",
								'cn' => '='
							]);
							bot('sendMessage', [
								'chat_id' => $fromid,
								'text' => "Yaxshi. Endi menga testning tugash vaqtini yuboring!👇\n\n2022-09-15 20:58:00"
							]);
						}
					}else{
						$quiz = $db->selectWhere('quiz',[
							'id' => 0,
							'cn' => '>'
						], " ORDER BY id DESC");
						if ($quiz->num_rows) {
							$quiz_data = mysqli_fetch_assoc($quiz);
							$db->update('quiz',[
								'quiz_desc' => "$text",
							],[
								'id' => "$quiz_data[id]",
								'cn' => '='
							]);
							bot('sendMessage', [
								'chat_id' => $fromid,
								'text' => "Yaxshi. Endi menga testning tugash vaqtini yuboring!👇\n\n2022-09-15 20:58:00"
							]);
						}
					}
					file_put_contents("step/addquiz_$fromid.tmp", "quiz_time");
				}else if ($quizStep == "quiz_time") {
					$time = strtotime($text);
					$quiz = $db->selectWhere('quiz',[
						'id' => 0,
						'cn' => '>'
					], " ORDER BY id DESC");
					if ($quiz->num_rows) {
						$quiz_data = mysqli_fetch_assoc($quiz);
						$db->update('quiz',[
							'quiz_time' => "$time",
						],[
							'id' => "$quiz_data[id]",
							'cn' => '='
						]);
					}
					$quiz = $db->selectWhere('quiz',[
						'id' => 0,
						'cn' => '>'
					], " ORDER BY id DESC");
					if ($quiz->num_rows) {
						$quiz_data = mysqli_fetch_assoc($quiz);
						$time = date("Y-m-d H:i:s", $quiz_data['quiz_time']);
						bot('sendMessage', [
							'chat_id' => $fromid,
							'text' => "✅ Test yartaildi!\n\n<b>Test nomi:</b> $quiz_data[quiz_title]\n<b>Test tavsifi: </b>$quiz_data[quiz_desc]\n<b>Test tuagsh vaqti: </b>$time\n\n✅ Test nomi va tavsifi to'g'ri bo'lsa savol qo'shishni boshlang!",
							'parse_mode' => "HTML",
							'reply_markup' => json_encode([
								'inline_keyboard'=>[
									[['text' => "Savol qo'shish ✅", 'callback_data' => "add_quest_$quiz_data[id]"], ['text' => "Bekor qilish 🚫", 'callback_data' => "quiz_cancel_$quiz_data[id]"]]
								],
							])
						]);
						array_map('unlink', glob("step/addquiz_$fromid.*"));
					}
				}

				if ($text == "/completion") {
					$quizId = file_get_contents("step/quiz_id_$fromid.tmp");
					if ($quizId) {
						$quizId = file_get_contents("step/quiz_id_$fromid.tmp");
						$questions = $db->selectWhere('questions',[
							'quiz_id' => 0,
							'cn' => '>'
						]);
						$quest = 0;
						foreach ($questions as $key => $value) {
							if ($value['quiz_id'] == $quizId) {
								$quest += 1;
							}
						}
						bot('sendMessage', [
							'chat_id' => $fromid,
							'text' => "✅ Test muvofaqiyatli yaratilidi\n\n📝 Testlar soni: $quest"."ta"
						]);
						array_map('unlink', glob("step/answer_$fromid.*"));
						array_map('unlink', glob("step/addQuest_$fromid.*"));
						array_map('unlink', glob("step/count_admin.*"));
						array_map('unlink', glob("step/quiz_id_$fromid.*"));	
					}else{
						bot('sendMessage', [
							'chat_id' => $fromid,
							'text' => "⚠️ Oldin test yarating!"
						]);
					}
				}

				$questStep = file_get_contents("step/addQuest_$fromid.tmp");
				if ($questStep == "quest_choose_type") {
					if ($photo) {
						$quizId = file_get_contents("step/quiz_id_$fromid.tmp");
						$photo_file_id = (($photo[2]->file_id) ? $photo[2]->file_id : $photo[1]->file_id);
						$db->insertInto('questions', ['fromid' => "$fromid",'quiz_id' => "$quizId", 'question' => $photo_file_id]);
						$question = $db->selectWhere('questions',[
							'id' => 0,
							'cn' => '>'
						], " ORDER BY id DESC");
						$question_data = mysqli_fetch_assoc($question);
						bot('sendPhoto', [
							'chat_id' => $fromid,
							'photo' => $photo_file_id,
							'caption' => "✅ To'gri javob qilib biror variantni belgilang!\n\n🏁 Testni tugatish uchun: /completion",
							'reply_markup' => $quizBtns
						]);
						file_put_contents("step/addQuest_$fromid.tmp", "true_ans");
						file_put_contents("step/answer_$fromid.json", json_encode(array('a' => false,'b' => false,'c' => false,'d' => false)));
					}else{
						$quizId = file_get_contents("step/quiz_id_$fromid.tmp");
						$db->insertInto('questions', ['fromid' => "$fromid",'quiz_id' => "$quizId", 'question_text' => $text]);
						$question = $db->selectWhere('questions',[
							'id' => 0,
							'cn' => '>'
						], " ORDER BY id DESC");
						$question_data = mysqli_fetch_assoc($question);
						bot('sendMessage', [
							'chat_id' => $fromid,
							'text' => $question_data['question_text']."\n\n✅ To'gri javob qilib biror variantni belgilang!\n🏁 Testni tugatish uchun: /completion",
							'reply_markup' => $quizBtns
						]);
						file_put_contents("step/addQuest_$fromid.tmp", "true_ans");
						file_put_contents("step/answer_$fromid.json", json_encode(array('a' => false,'b' => false,'c' => false,'d' => false)));
					}
				}
		    }
		}
		if (!is_null($update->callback_query)) {
			if (in_array($cbid, $admins)) {
				if ($data == 'add_admin') {
					bot('editMessageText',[
						'chat_id'=>$cbid,
						'message_id'=>$mid,
						'text'=>"Yangi admin qo'shish uchun yangi admin habaridan forward yuboring.\n\nEslatma: Yangi admin sozlamarida uzatilgan habar hamma uchun yoniq bo'lish zarur yo'qsa yangi admin ID sini olishning imkoni bo'lmaydi!",
						'reply_markup'=>$calncel_add_admin
					]);
					file_put_contents('helpers/admin/json/' . $cbid . '.json', json_encode(array('menu'=>'add_admin','step'=>0)));
				}
				if ($data == 'calncel_add_admin') {
					bot('editMessageText',[
						'chat_id'=>$cbid,
						'message_id'=>$mid,
						'text'=>"Yangi admin qo'shish bekor qilindi!",
						'reply_markup'=>$home_keyboard
					]);
					file_put_contents('helpers/admin/json/' . $cbid . '.json', '');
				}
				if ($data == 'delete_admin') {
					if (file_exists('config/json/admins.json')) {
						$bot_admins = "\n";
					    foreach ($admins as $key => $value) {
					        $bot_admins .= ($key+=1) . " - /del_admin_" . $value . "\n";
					    }
						bot('editMessageText',[
							'chat_id'=>$cbid,
							'message_id'=>$mid,
							'text'=>"Bot adminlari royxati:\n" . $bot_admins,
							'reply_markup'=>$cancel_home
						]);
					}
				}
				if ($data == 'remove_channel') {
					$channels = $db->selectWhere('quiz_channels',[
		                'id'=>0,
		                'cn'=>'>'
		            ]);
					if ($channels->num_rows > 1) {
						$bot_channels = "\n";
						$i = -1;
					    foreach ($channels as $key => $value) {
					    	$i++;
					    	if ($i == 0) {
					    		continue;
					    	}
			                $getchat = bot('getChat',[
			                    'chat_id'=>$value["object"],
			                ]);
                			$title = $getchat->result->title;
					        $bot_channels .= $i . ") " . $title . " - /del_channel_" . $value["id"] . "\n";
					    }
						bot('editMessageText',[
							'chat_id'=>$cbid,
							'message_id'=>$mid,
							'text'=>"Botga biriktirilgan kanallar royxati:\nBiror kanal o'chirish uchun /del_channel_ va raqam ustuga bosing\n" . $bot_channels,
							'reply_markup'=>$cancel_home
						]);
					}else{
						bot('editMessageText',[
							'chat_id'=>$cbid,
							'message_id'=>$mid,
							'text'=>"Botga hechqanday kanal ulanmagan.",
							'reply_markup'=>$setting_channel
						]);
					}
				}
				if ($data == 'setting_channel') {
					bot('editMessageText',[
						'chat_id'=>$cbid,
						'message_id'=>$mid,
						'text'=>"Kanal sozlash bo'limi.\nNima qilamiz?",
						'reply_markup'=>$setting_channel
					]);
				}
				if ($data == 'add_channel') {
					bot('editMessageText',[
						'chat_id'=>$cbid,
						'message_id'=>$mid,
						'text'=>"Kanal qo'shish uchun kanal usernameni yuboring.\nQuyidagi formatda @okdeveloper",
						'reply_markup'=>$cancel_home
					]);
					file_put_contents('helpers/admin/json/' . $cbid . '.json', json_encode(array('menu'=>'add_channel','step'=>0)));
				}
				if ($data == 'channel_on') {
					$db->update('quiz_channels',[
						'object'=>"on",
					],[
						'name'=>"status",
						'cn'=>'='
					]);
					bot('editMessageText',[
						'chat_id'=>$cbid,
						'message_id'=>$mid,
						'text'=>"Majburiy azolik On rejimga o'tkazildi.",
						'reply_markup'=>$setting_channel
					]);
				}
				if ($data == 'channel_off') {
					$db->update('quiz_channels',[
						'object'=>"off",
					],[
						'name'=>"status",
						'cn'=>'='
					]);
					bot('editMessageText',[
						'chat_id'=>$cbid,
						'message_id'=>$mid,
						'text'=>"Majburiy azolik Off rejimga o'tkazildi.",
						'reply_markup'=>$setting_channel
					]);
				}
				if ($data == 'send_ads') {
					$check_send_lang_type = json_decode(file_get_contents('config/json/check_type.json'));
					$check_send_lang = json_encode([
						'inline_keyboard'=>[
							[['text'=>"🇷🇺 Rus userlarga " . (($check_send_lang_type->ru) ? '✅':'❌'), 'callback_data'=>'check_send_lang_ru'],['text'=>"🇺🇸 Ingliz userlarga " . (($check_send_lang_type->eng) ? '✅':'❌'), 'callback_data'=>'check_send_lang_eng'],],
							[['text'=>"🇺🇿 Uzbek userlarga " . (($check_send_lang_type->uz) ? '✅':'❌'), 'callback_data'=>'check_send_lang_uz'],],
							[['text'=>"Til tanlamaganlar " . (($check_send_lang_type->not_selected) ? '✅':'❌'), 'callback_data'=>'check_send_lang_nolang'],],
							[['text'=>"Guruhlarga " . (($check_send_lang_type->group) ? '✅':'❌'), 'callback_data'=>'check_send_lang_group'],],
							[['text'=>"Ortga", 'callback_data'=>'cancel_home'],],
						],
					]);
					bot('editMessageText',[
						'chat_id'=>$cbid,
						'message_id'=>$mid,
						'text'=>"Reklama yuborish uchun ixtiyoriy habar yuboring.",
						'reply_markup'=>$check_send_lang
					]);
					file_put_contents('helpers/admin/json/' . $cbid . '.json', json_encode(array('menu'=>'send_ads','step'=>0)));
					file_put_contents('config/json/check_type.json', json_encode(array('uz'=>true,'ru'=>true,'eng'=>true,'group'=>true,'not_selected'=>true)));
				}
				if ($data == 'cancel_home') {
					bot('editMessageText',[
						'chat_id'=>$cbid,
						'message_id'=>$mid,
						'text'=>"Bosh sahifa.",
						'reply_markup'=>$home_keyboard
					]);
					file_put_contents('helpers/admin/json/' . $cbid . '.json', '');
				}
				if ($data == 'calncel_send_ads') {
					bot('editMessageText',[
						'chat_id'=>$cbid,
						'message_id'=>$mid,
						'text'=>"Bosh sahifa.",
						'reply_markup'=>$home_keyboard
					]);
					file_put_contents('helpers/admin/json/' . $cbid . '.json', '');
					file_put_contents('config/json/sendMessage.json', '');
				}
				if ($data == 'confirm_send_ads') {
					bot('editMessageText',[
						'chat_id'=>$cbid,
						'message_id'=>$mid,
						'text'=>"Yuborish boshlanmoqda.",
					]);
					file_put_contents('helpers/send_start.txt', '0');
					file_put_contents('helpers/send_confirm.txt', 'send');
					file_put_contents('helpers/admin/json/' . $fromid . '.json', '');
				}
				if (mb_stripos($data, 'check_send_lang_')!==false) {
					$check_send_lang_type = json_decode(file_get_contents('config/json/check_type.json'));
					if ($data == 'check_send_lang_ru') {
						($check_send_lang_type->ru) ? $check_send_lang_type->ru = false : $check_send_lang_type->ru = true;
					}
					if ($data == 'check_send_lang_eng') {
						($check_send_lang_type->eng) ? $check_send_lang_type->eng = false : $check_send_lang_type->eng = true;
					}
					if ($data == 'check_send_lang_uz') {
						($check_send_lang_type->uz) ? $check_send_lang_type->uz = false : $check_send_lang_type->uz = true;
					}
					if ($data == 'check_send_lang_group') {
						($check_send_lang_type->group) ? $check_send_lang_type->group = false : $check_send_lang_type->group = true;
					}
					if ($data == 'check_send_lang_nolang') {
						($check_send_lang_type->not_selected) ? $check_send_lang_type->not_selected = false : $check_send_lang_type->not_selected = true;
					}
					file_put_contents('config/json/check_type.json', json_encode($check_send_lang_type));
					$check_send_lang_type = json_decode(file_get_contents('config/json/check_type.json'));
					$check_send_lang = json_encode([
						'inline_keyboard'=>[
							[['text'=>"🇷🇺 Rus userlarga " . (($check_send_lang_type->ru) ? '✅':'❌'), 'callback_data'=>'check_send_lang_ru'],['text'=>"🇺🇸 Ingliz userlarga " . (($check_send_lang_type->eng) ? '✅':'❌'), 'callback_data'=>'check_send_lang_eng'],],
							[['text'=>"🇺🇿 Uzbek userlarga " . (($check_send_lang_type->uz) ? '✅':'❌'), 'callback_data'=>'check_send_lang_uz'],],
							[['text'=>"Til tanlamaganlar " . (($check_send_lang_type->not_selected) ? '✅':'❌'), 'callback_data'=>'check_send_lang_nolang'],],
							[['text'=>"Guruhlarga " . (($check_send_lang_type->group) ? '✅':'❌'), 'callback_data'=>'check_send_lang_group'],],
							[['text'=>"Ortga", 'callback_data'=>'cancel_home'],],
						],
					]);
					bot('editMessageText',[
						'chat_id'=>$cbid,
						'message_id'=>$mid,
						'text'=>"Reklama yuborish uchun ixtiyoriy habar yuboring.",
						'reply_markup'=>$check_send_lang
					]);
					bot('answerCallbackQuery',[
						'callback_query_id'=>$qid,
						'text'=>"Userlarga yuborish o'zgartirildi!",
						'show_alert'=>true
					]);
				}
				if ($data == "add_quiz") {
					bot('editMessageText', [
						'chat_id' => $cbid,
						'message_id' => $mid,
						'text' => "Test qo'shish uchun test nomini kiriting"
					]);
					file_put_contents("step/addquiz_$cbid.tmp", "quiz_title");
				}
				if (mb_stripos($data, "quiz_cancel_") !== false) {
					$exp = explode("quiz_cancel_", $data);
					$quiz_id = $exp[1];
					$db->delete('quiz',[
						[
							'id' => trim($quiz_id),
							'cn' => '='
						],
					]);
					bot('editMessageText', [
						'chat_id' => $cbid,
						'message_id' => $mid,
						'text' => "🚫 Test yaratish bekor qilindi!"
					]);
				}
				if (mb_stripos($data, "add_quest_") !== false) {
					$exp = explode("add_quest_", $data);
					$quiz_id = $exp[1];
					bot('editMessageText', [
						'chat_id' => $cbid,
						'message_id' => $mid,
						'text' => "1-savolni kiriting!\n\nSavolni rasmi bo'lsa shunchaki rasm yuboring yoki matnli bo'lsa ushbu ko'rinishda yuboring 👇\n\nSavol matni?\n\nA) A variant\nB) B variant\nC) C variant\nD) D variant"
					]);
					file_put_contents("step/addQuest_$cbid.tmp", "quest_choose_type");
					file_put_contents("step/quiz_id_$cbid.tmp", "$quiz_id");
					file_put_contents("step/count_admin.txt", 1);
				}
				if (mb_stripos($data, "ans_") !== false) {
					$answer = json_decode(file_get_contents("step/answer_$cbid.json"));
					if ($data == "ans_a") {
						$ans_a = ($answer->a) ? $answer->a = false : $answer->a = true;
						file_put_contents("step/answer_$cbid.json", json_encode(array("a" => $ans_a,'b' => false,'c' => false,'d' => false)));
						$question = $db->selectWhere('questions',[
							'id' => 0,
							'cn' => '>'
						], " ORDER BY id DESC");
						$question_data = mysqli_fetch_assoc($question);
						$db->update('questions',[
							't' => "A",
						],[
							'id' => "$question_data[id]",
							'cn' => '='
						]);
					}
					if ($data == "ans_b") {
						$ans_b = ($answer->b) ? $answer->b = false : $answer->b = true;
						file_put_contents("step/answer_$cbid.json", json_encode(array('a' => false,"b" => $ans_b,'c' => false,'d' => false)));
						$question = $db->selectWhere('questions',[
							'id' => 0,
							'cn' => '>'
						], " ORDER BY id DESC");
						$question_data = mysqli_fetch_assoc($question);
						$db->update('questions',[
							't' => "B",
						],[
							'id' => "$question_data[id]",
							'cn' => '='
						]);
					}
					if ($data == "ans_c") {
						$ans_c = ($answer->c) ? $answer->c = false : $answer->c = true;
						file_put_contents("step/answer_$cbid.json", json_encode(array('a' => false,'b' => false,"c" => $ans_c,'d' => false)));
						$question = $db->selectWhere('questions',[
							'id' => 0,
							'cn' => '>'
						], " ORDER BY id DESC");
						$question_data = mysqli_fetch_assoc($question);
						$db->update('questions',[
							't' => "C",
						],[
							'id' => "$question_data[id]",
							'cn' => '='
						]);
					}
					if ($data == "ans_d") {
						$ans_d = ($answer->d) ? $answer->d = false : $answer->d = true;
						file_put_contents("step/answer_$cbid.json", json_encode(array('a' => false,'b' => false,'c' => false,"d" => $ans_d)));
						$question = $db->selectWhere('questions',[
							'id' => 0,
							'cn' => '>'
						], " ORDER BY id DESC");
						$question_data = mysqli_fetch_assoc($question);
						$db->update('questions',[
							't' => "D",
						],[
							'id' => "$question_data[id]",
							'cn' => '='
						]);
					}
					// file_put_contents("step/answer_$cbid.json", json_encode($answer));
					$answer = json_decode(file_get_contents("step/answer_$cbid.json"));
					$quizBtns2 = json_encode([
						'inline_keyboard' => [
							[['text' => "A " . (($answer->a) ? "✅" : "❌"), 'callback_data' => "ans_a"],['text' => "B " . (($answer->b) ? "✅" : "❌"), 'callback_data' => "ans_b"],],
							[['text' => "C " . (($answer->c) ? "✅" : "❌"), 'callback_data' => "ans_c"],['text' => "D " . (($answer->d) ? "✅" : "❌"), 'callback_data' => "ans_d"],],
							[['text' => "Keyngisi ➡️", 'callback_data' => "addNextQuest"]]
						],
					]);
					bot('editMessageReplyMarkup',[
						'chat_id' => $cbid,
						'message_id' => $mid,
						// 'text'=>"Reklama yuborish uchun ixtiyoriy habar yuboring.",
						'reply_markup' => $quizBtns2
					]);
					$question = $db->selectWhere('questions',[
						'id' => 0,
						'cn' => '>'
					], " ORDER BY id DESC");
					$question_data = mysqli_fetch_assoc($question);
					bot('answerCallbackQuery',[
						'callback_query_id' => $qid,
						'text' => "✅ To'gri javob qilib «$question_data[t]» tanlandi!",
						'show_alert' => true
					]);
				}
				if ($data == "addNextQuest") {
					array_map('unlink', glob("step/answer_$cbid.*"));
					$count = file_get_contents("step/count_admin.txt");
					$count = $count + 1;
					bot("deleteMessage", [
						'chat_id' => $cbid,
						'message_id' => $mid
					]);
					bot('sendMessage', [
						'chat_id' => $cbid,
						'text' => "$count-savolni kiriting!\n\nSavolni rasmi bo'lsa shunchaki rasm yuboring yoki matnli bo'lsa ushbu ko'rinishda yuboring 👇\n\nSavol matni?\n\nA) A variant\nB) B variant\nC) C variant\nD) D variant"
					]);
					file_put_contents("step/addQuest_$cbid.tmp", "quest_choose_type");
					file_put_contents("step/count_admin.txt", $count);
				}
			}
		}
	}
?>