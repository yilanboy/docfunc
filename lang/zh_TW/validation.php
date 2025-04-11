<?php

declare(strict_types=1);

return [
    'accepted' => '必須接受 :attribute。',
    'accepted_if' => '當 :other 為 :value 時，:attribute 必須接受。',
    'active_url' => ':attribute 不是有效的網址。',
    'after' => ':attribute 必須要晚於 :date。',
    'after_or_equal' => ':attribute 必須要等於 :date 或更晚。',
    'alpha' => ':attribute 只能以字母組成。',
    'alpha_dash' => ':attribute 只能以字母、數字、連接線(-)及底線(_)組成。',
    'alpha_num' => ':attribute 只能以字母及數字組成。',
    'array' => ':attribute 必須為陣列。',
    'ascii' => ':attribute 必須僅包含單字節字母數字字符和符號。',
    'before' => ':attribute 必須要早於 :date。',
    'before_or_equal' => ':attribute 必須要等於 :date 或更早。',
    'between' => [
        'array' => ':attribute: 必須有 :min - :max 個元素。',
        'file' => ':attribute 必須介於 :min 至 :max KB 之間。',
        'numeric' => ':attribute 必須介於 :min 至 :max 之間。',
        'string' => ':attribute 必須介於 :min 至 :max 個字元之間。',
    ],
    'boolean' => ':attribute 必須為布林值。',
    'can' => ':attribute 欄位包含未經授權的值。',
    'confirmed' => ':attribute 確認欄位的輸入不一致。',
    'contains' => ':attribute 欄位缺少必要的值。',
    'current_password' => '當前密碼不正確。',
    'date' => ':attribute 不是有效的日期。',
    'date_equals' => ':attribute 必須等於 :date。',
    'date_format' => ':attribute 不符合 :format 的格式。',
    'decimal' => ':attribute 必須有 :decimal 位小數。',
    'declined' => ':attribute 必須拒絕。',
    'declined_if' => '當 :other 為 :value 時，:attribute 必須拒絕。',
    'different' => ':attribute 與 :other 必須不同。',
    'digits' => ':attribute 必須是 :digits 位數字。',
    'digits_between' => ':attribute 必須介於 :min 至 :max 位數字。',
    'dimensions' => ':attribute 圖片尺寸不正確。',
    'distinct' => ':attribute 已經存在。',
    'doesnt_end_with' => ':attribute 不能以下列之一結尾：:values。',
    'doesnt_start_with' => ':attribute 不能以下列之一開頭：:values。',
    'email' => ':attribute 必須是有效的 E-mail。',
    'ends_with' => ':attribute 結尾必須包含下列之一：:values。',
    'enum' => ':attribute 的值不正確。',
    'exists' => ':attribute 不存在。',
    'extensions' => ':attribute 欄位必須具有以下副檔名之一：:values。',
    'file' => ':attribute 必須是有效的檔案。',
    'filled' => ':attribute 不能留空。',
    'gt' => [
        'array' => ':attribute 必須多於 :value 個元素。',
        'file' => ':attribute 必須大於 :value KB。',
        'numeric' => ':attribute 必須大於 :value。',
        'string' => ':attribute 必須多於 :value 個字元。',
    ],
    'gte' => [
        'array' => ':attribute 必須多於或等於 :value 個元素。',
        'file' => ':attribute 必須大於或等於 :value KB。',
        'numeric' => ':attribute 必須大於或等於 :value。',
        'string' => ':attribute 必須多於或等於 :value 個字元。',
    ],
    'hex_color' => ':attribute 欄位必須是有效的十六進位顏色。',
    'image' => ':attribute 必須是一張圖片。',
    'in' => '所選擇的 :attribute 選項無效。',
    'in_array' => ':attribute 沒有在 :other 中。',
    'integer' => ':attribute 必須是一個整數。',
    'ip' => ':attribute 必須是一個有效的 IP 位址。',
    'ipv4' => ':attribute 必須是一個有效的 IPv4 位址。',
    'ipv6' => ':attribute 必須是一個有效的 IPv6 位址。',
    'json' => ':attribute 必須是正確的 JSON 字串。',
    'list' => ':attribute 欄位必須是列表。',
    'lowercase' => ':attribute 必須小寫。',
    'lt' => [
        'array' => ':attribute 必須少於 :value 個元素。',
        'file' => ':attribute 必須小於 :value KB。',
        'numeric' => ':attribute 必須小於 :value。',
        'string' => ':attribute 必須少於 :value 個字元。',
    ],
    'lte' => [
        'array' => ':attribute 必須少於或等於 :value 個元素。',
        'file' => ':attribute 必須小於或等於 :value KB。',
        'numeric' => ':attribute 必須小於或等於 :value。',
        'string' => ':attribute 必須少於或等於 :value 個字元。',
    ],
    'mac_address' => ':attribute 必須是一個有效的 MAC 位址。',
    'max' => [
        'array' => ':attribute 最多有 :max 個元素。',
        'file' => ':attribute 不能大於 :max KB。',
        'numeric' => ':attribute 不能大於 :max。',
        'string' => ':attribute 不能多於 :max 個字元。',
    ],
    'max_digits' => ':attribute 不得超過 :max 位。',
    'mimes' => ':attribute 必須為 :values 的檔案。',
    'mimetypes' => ':attribute 必須為 :values 的檔案。',
    'min' => [
        'array' => ':attribute 至少有 :min 個元素。',
        'file' => ':attribute 不能小於 :min KB。',
        'numeric' => ':attribute 不能小於 :min。',
        'string' => ':attribute 不能小於 :min 個字元。',
    ],
    'min_digits' => ':attribute 必須至少有 :min 位數字。',
    'missing' => '必須缺少 :attribute 欄位。',
    'missing_if' => '當 :other 為 :value 時，必須缺少 :attribute 欄位。',
    'missing_unless' => '必須缺少 :attribute 欄位，除非 :other 是 :value。',
    'missing_with' => '存在 :values 時，必須缺少 :attribute 欄位。',
    'missing_with_all' => '存在 :values 時，必須缺少 :attribute 欄位。',
    'multiple_of' => '所選擇的 :attribute 必須為 :value 中的多個。',
    'not_in' => '所選擇的 :attribute 選項無效。',
    'not_regex' => ':attribute 的格式錯誤。',
    'numeric' => ':attribute 必須為一個數字。',
    'password' => [
        'letters' => ':attribute 必須至少包含一個字母。',
        'mixed' => ':attribute 必須至少包含一個大寫字母和一個小寫字母。',
        'numbers' => ':attribute 必須至少包含一個數字。',
        'symbols' => ':attribute 必須包含至少一個符號。',
        'uncompromised' => '給定的 :attribute 已出現數據洩漏。請選擇不同的 :attribute。',
    ],
    'present' => ':attribute 必須存在。',
    'present_if' => '當 :other 等於 :value 時，必須存在 :attribute 個欄位。',
    'present_unless' => '除非 :other 等於 :value，否則 :attribute 個欄位必須存在。',
    'present_with' => '當 :values 存在時，:attribute 個欄位必須存在。',
    'present_with_all' => '當存在 :values 時，必須存在 :attribute 個欄位。',
    'prohibited' => ':attribute 欄位被禁止。',
    'prohibited_if' => '当 :other 为 :value 时，:attribute 欄位被禁止。',
    'prohibited_if_accepted' => ':attribute 欄位在 :other 被接受時不允許填寫。',
    'prohibited_if_declined' => ':attribute 欄位在 :other 被拒絕時不允許填寫。',
    'prohibited_unless' => ':attribute 欄位被禁止，除非 :other 在 :values 中。',
    'prohibits' => ':attribute 欄位禁止包含 :other。',
    'regex' => ':attribute 的格式錯誤。',
    'required' => ':attribute 不能留空。',
    'required_array_keys' => ':attribute 必須包含 :values 中的一個鍵。',
    'required_if' => '當 :other 是 :value 時 :attribute 不能留空。',
    'required_if_accepted' => '接受 :other 時需要 :attribute 欄位。',
    'required_if_declined' => '拒絕 :other 時需要 :attribute 欄位。',
    'required_unless' => '當 :other 不是 :values 時 :attribute 不能留空。',
    'required_with' => '當 :values 出現時 :attribute 不能留空。',
    'required_with_all' => '當 :values 出現時 :attribute 不能為空。',
    'required_without' => '當 :values 未出現時，:attribute 不能留空。',
    'required_without_all' => '當 :values 都不出現時 :attribute 不能留空。',
    'same' => ':attribute 與 :other 必須相同。',
    'size' => [
        'array' => ':attribute 必須是 :size 個元素。',
        'file' => ':attribute 的大小必須是 :size KB。',
        'numeric' => ':attribute 的大小必須是 :size。',
        'string' => ':attribute 必須是 :size 個字元。',
    ],
    'starts_with' => ':attribute 開頭必須包含下列之一：:values。',
    'string' => ':attribute 必須是一個字串。',
    'timezone' => ':attribute 必須是一個正確的時區值。',
    'ulid' => ':attribute 必須是有效的 ULID。',
    'unique' => ':attribute 已經存在。',
    'uploaded' => ':attribute 上傳失敗。',
    'uppercase' => ':attribute 必須大寫。',
    'url' => ':attribute 的格式錯誤。',
    'uuid' => ':attribute 必須是有效的 UUID。',
    'attributes' => [
        'address' => '地址',
        'affiliate_url' => '附屬網址',
        'age' => '年齡',
        'amount' => '數量',
        'announcement' => '公告',
        'area' => '區域',
        'audience_prize' => '觀眾獎',
        'audience_winner' => 'audience winner',
        'available' => '可用的',
        'birthday' => '生日',
        'body' => '身體',
        'city' => '城市',
        'company' => 'company',
        'compilation' => '彙編',
        'concept' => '概念',
        'conditions' => '狀況',
        'content' => '內容',
        'contest' => 'contest',
        'country' => '國家',
        'cover' => '覆蓋',
        'created_at' => '創建於',
        'creator' => '創造者',
        'currency' => '貨幣',
        'current_password' => '當前密碼',
        'customer' => '顧客',
        'date' => '日期',
        'date_of_birth' => '出生日期',
        'dates' => '日期',
        'day' => '天',
        'deleted_at' => '刪除於',
        'description' => '描述',
        'display_type' => '顯示類型',
        'district' => '區',
        'duration' => '期間',
        'email' => 'e-mail',
        'excerpt' => '摘要',
        'filter' => '篩選',
        'finished_at' => '完成於',
        'first_name' => '名',
        'gender' => '性別',
        'grand_prize' => '大獎',
        'group' => '團體',
        'hour' => '時',
        'image' => '圖片',
        'image_desktop' => '桌面影像',
        'image_main' => '主圖',
        'image_mobile' => '移動影像',
        'images' => '圖片',
        'is_audience_winner' => '是觀眾贏家',
        'is_hidden' => '被隱藏',
        'is_subscribed' => '已訂閱',
        'is_visible' => '是可見的',
        'is_winner' => '是贏家',
        'items' => '專案',
        'key' => '鑰匙',
        'last_name' => '姓',
        'lesson' => '課',
        'line_address_1' => '行地址 1',
        'line_address_2' => '行地址 2',
        'login' => '登入',
        'message' => '信息',
        'middle_name' => '中間名字',
        'minute' => '分',
        'mobile' => '手機',
        'month' => '月',
        'name' => '名稱',
        'national_code' => '國家代碼',
        'number' => '數字',
        'password' => '密碼',
        'password_confirmation' => '確認密碼',
        'phone' => '電話',
        'photo' => '照片',
        'portfolio' => '資料夾',
        'postal_code' => '郵政編碼',
        'preview' => '預覽',
        'price' => '價格',
        'product_id' => '產品編號',
        'product_uid' => '產品UID',
        'product_uuid' => '產品UUID',
        'promo_code' => '促銷代碼',
        'province' => '省',
        'quantity' => '數量',
        'reason' => '原因',
        'recaptcha_response_field' => '重新驗證響應字段',
        'referee' => '裁判',
        'referees' => '裁判員',
        'reject_reason' => '拒絕理由',
        'remember' => '記住',
        'restored_at' => '恢復於',
        'result_text_under_image' => '圖片下方的結果文本',
        'role' => '角色',
        'rule' => '規則',
        'rules' => '規則',
        'second' => '秒',
        'sex' => '性別',
        'shipment' => '運輸',
        'short_text' => '短文',
        'size' => '大小',
        'skills' => '技能',
        'slug' => '蛞蝓',
        'specialization' => '專業化',
        'started_at' => '開始於',
        'state' => '狀態',
        'status' => '地位',
        'street' => '街道',
        'student' => '學生',
        'subject' => '主題',
        'tag' => '標籤',
        'tags' => '標籤',
        'teacher' => '老師',
        'terms' => '條款',
        'test_description' => '測試說明',
        'test_locale' => '測試語言環境',
        'test_name' => '測試名稱',
        'text' => '文本',
        'time' => '時間',
        'title' => '標題',
        'type' => '類型',
        'updated_at' => '更新於',
        'upload' => '文章中的圖片',
        'user' => '使用者',
        'username' => '使用者名稱',
        'value' => '價值',
        'winner' => 'winner',
        'work' => 'work',
        'year' => '年',
    ],
];
