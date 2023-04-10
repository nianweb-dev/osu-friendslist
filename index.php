<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>OSU!好友列表生成器</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mdui/dist/css/mdui.min.css" />
  </head>
  <body>
    <div class="mdui-container">
      <div class="mdui-typo">
        <h1>OSU!好友列表读取器</h1>
        <p>这个页面只是对osu!的oauth2 api进行的简单包装实现，并没有实现安全防护以及防滥用功能，请勿部署于生产环境给他人使用。</p>
        <form class="mdui-textfield">
          <textarea class="mdui-textfield-input" placeholder="请输入文本" readonly="readonly" rows="10"><?php

// 应用id和密钥
$client_id = '';
$client_secret = '';
// 回调链接
$redirect_uri = '';





//
// 获取code返回值
$code = $_GET['code'];

// 如果code为空，则输出提示，否则进入下一步
if (empty($code)) {
    echo "点击授权读取来生成。";
} else {

// 令牌请求链接
$token_url = 'https://osu.ppy.sh/oauth/token';

// POST请求参数
$post_params = array(
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $redirect_uri
);

// 初始化cURL
$ch = curl_init($token_url);

// 设置cURL选项
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
    'Content-Type: application/x-www-form-urlencoded'
));

// 发送POST请求并获取响应
$response = curl_exec($ch);

// 关闭cURL
curl_close($ch);

// 解析响应，获取令牌
$access_token = json_decode($response)->access_token;

// 如果令牌为空，则输出错误提示，并返回osu!  api响应的内容以供调试
if (empty($access_token)) {
    echo "错误：OSU! api返回以下内容";
    echo $response;
    echo "错误：没有在服务器回复中发现令牌。";
} else {

//直接打印出输出令牌 (危险，仅供测试)
//echo "操作令牌：$access_token";
//echo "code : $code";
//echo "tag : $response";

// 获取朋友列表请求链接
$friends_url = 'https://osu.ppy.sh/api/v2/friends';

// 初始化cURL
$ch = curl_init($friends_url);

// 设置cURL选项
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Authorization: Bearer $access_token"
));

// 发送GET请求并获取响应
$response = curl_exec($ch);
//
// 关闭cURL
curl_close($ch);
//
// 解析响应，获取朋友列表
$friendsData = json_decode($response);
//
// 启动输出缓冲，从此处开始打印的内容不直接发送给浏览器，而是保存到缓冲区
ob_start();
//
// 遍历数组的朋友列表并打印出名字和个人资料链接，并且用BBcode进行格式化
foreach ($friendsData as $friend) {
        $username = $friend->username;
        $id = $friend->id;
        $profileColour = $friend->profile_colour;
	//如果检测到profileColour参数不为空则使用另外一个带有color标签的模板，通常管理员才能拥有用户颜色参数
        if ($profileColour === null) {
            echo "[url=https://osu.ppy.sh/u/{$id}]{$username}[/url]\n";
        } else {
            echo "[color={$profileColour}][url=https://osu.ppy.sh/u/{$id}]{$username}[/url][/color]\n";
        }
}
// 从缓冲区中读取内容保存到变量
$friendsList = ob_get_clean();

printf($friendsList);
  }
}
?></textarea>
	</form>
        <div class="mdui-btn-group">
          <button class="mdui-btn mdui-btn-raised mdui-ripple" id="copy-btn">复制</button>
          <a class="mdui-btn mdui-btn-raised mdui-ripple" href="https://osu.ppy.sh/oauth/authorize?client_id=<?php print $client_id; ?>&redirect_uri=<?php print $redirect_uri; ?>&scope=identify+friends.read&response_type=code" target="_self">读取</a>
          <a class="mdui-btn mdui-btn-raised mdui-ripple" href="https://osu.ppy.sh/home/account/edit#oauth" target="_self">退出登录</a>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/mdui/dist/js/mdui.min.js"></script>
    <script>
      // 获取文本框和按钮
      const textField = document.querySelector('.mdui-textfield-input');
      const copyBtn = document.querySelector('#copy-btn');

      // 点击“复制”按钮时选中文本框，将文本框内容复制到剪贴板中
      copyBtn.addEventListener('click', () => {
        textField.select();
        document.execCommand('copy');
        mdui.snackbar({
          message: '已复制到剪贴板',
          position: 'right-bottom'
        });
      });
    </script>
  </body>
</html>
