<?php
namespace Aex\Packagetest;
use Illuminate\Session\SessionManager;
use Illuminate\Config\Repository;
use App\Http\Requests\Auth\Login as Request;

class Packagetest
{
    /**
     * @var SessionManager
     */
    protected $session;
    /**
     * @var Repository
     */
    protected $config;
    /**
     * Packagetest constructor.
     * @param SessionManager $session
     * @param Repository $config
     */
    public function __construct(SessionManager $session, Repository $config)
    {
        $this->session = $session;
        $this->config = $config;
    }


//    /**
//     * @param string $msg
//     * @return string
//     */
//    public function test_rtn($username = '',$password = '',$sessionKey){
//        //获取配置
//        $group = $this->config->get('packagetest.group');
//        $system = $this->config->get('packagetest.system');
//        $sysId = $this->config->get('packagetest.sysId');
//        $key = $this->config->get('packagetest.key');
//        $secret = $this->config->get('packagetest.secret');
//        $url = "http://manage.91cyt.com/api/auth/ldapLogin";
//
//        $postdata = [
//            "username" => $username,
//            "password" => $password,
//            "group" => $group,
//            "system" => $system,
//        ];
//        //秘钥生成
//        $sign = $this->sign($sysId,$key,$secret);
//        $userInfo = $this->curl_post($url,$postdata,$sign);
//        $userInfo = json_decode($userInfo,true);
////        dd($userInfo);
//        if ($userInfo["code"] == 200){
//            session()->put($sessionKey,9);
//            session()->put("dashboard_id",1);
//            session()->put("company_id",1);
//            session()->put("userInfo",$userInfo["data"]["userInfo"]);
//        }
//
//        $email = $request->get('email', false);
//        $password = $request->get('password', false);
//        $sessionKey = $this->getName();
//        $user = Packagetest::test_rtn($email,$password,$sessionKey);
//
//        if ($user["code"] != 200){
//            $response = [
//                'status' => null,
//                'success' => false,
//                'error' => true,
//                'message' => "账号或者密码错误",
//                'data' => null,
//                'redirect' => null,
//            ];
//            return response()->json($response);
//        }
////        dump($userInfo);
//        return $userInfo;
////        $config_arr = $this->config->get('packagetest.options');
////        return $msg.' <strong>from your custom develop package!</strong>>';
//    }


    public function getName()
    {
        return 'login_web_'.sha1("Illuminate\Auth\SessionGuard");
    }
    /**
     * @param string $msg
     * @return string
     */
    public function test_rtn(Request $request){
        //获取配置
        $group = $this->config->get('packagetest.group');
        $system = $this->config->get('packagetest.system');
        $sysId = $this->config->get('packagetest.sysId');
        $key = $this->config->get('packagetest.key');
        $secret = $this->config->get('packagetest.secret');
        $url = "http://manage.91cyt.com/api/auth/ldapLogin";

        $username = $request->get('email', false);
        $password = $request->get('password', false);
        $sessionKey = $this->getName();
        $postdata = [
            "username" => $username,
            "password" => $password,
            "group" => $group,
            "system" => $system,
        ];
        //秘钥生成
        $sign = $this->sign($sysId,$key,$secret);
        $userInfo = $this->curl_post($url,$postdata,$sign);
        $userInfo = json_decode($userInfo,true);
//        dd($userInfo);
        if ($userInfo["code"] == 200){
            session()->put($sessionKey,9);
            session()->put("dashboard_id",1);
            session()->put("company_id",1);
            session()->put("userInfo",$userInfo["data"]["userInfo"]);
        }

        if ($userInfo["code"] != 200){
            $response = [
                'status' => null,
                'success' => false,
                'error' => true,
                'message' => "账号或者密码错误",
                'data' => null,
                'redirect' => null,
            ];
        }else{
            $response = [
                'status' => null,
                'success' => true,
                'error' => false,
                'message' => null,
                'data' => null,
//            'redirect' => redirect()->intended(route($user->landing_page))->getTargetUrl(),
                'redirect' => redirect()->intended(route("dashboard"))->getTargetUrl(),
            ];
        }


        return response()->json($response);

//        dump($userInfo);
//        return $userInfo;
//        $config_arr = $this->config->get('packagetest.options');
//        return $msg.' <strong>from your custom develop package!</strong>>';
    }

    public function test(){
        dd(313423432);
    }

    /**
     * 秘钥生成
     */
    public function sign($sysId,$key,$secret){
        //获取六位随机数
        $salt = mt_rand(100000,999999);
        $time = time();
        $token = md5($salt . $time . $salt . $sysId . $key . $secret);
        $signInfo = [
            "sysId" => $sysId,
            "key" => $key,
            "secret" => $secret,
            "time" => $time,
            "salt" => $salt,
            "token" => $token,
        ];
//        dd($signInfo);

        $sign = base64_encode(json_encode($signInfo));
        return $sign;
    }

    /**
     *  post请求
     */
    public function curl_post( $url, $postdata,$sign ) {

        $header = array(
            'Accept: application/json',
            'Sign: '.$sign
        );

        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // 超时设置
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        // 超时设置，以毫秒为单位
        // curl_setopt($curl, CURLOPT_TIMEOUT_MS, 500);

        // 设置请求头
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE );

        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
        //执行命令
        $data = curl_exec($curl);

        // 显示错误信息
//        if (curl_error($curl)) {
//            print "Error: " . curl_error($curl);
//        } else {
            // 打印返回的内容
//            var_dump($data);
        curl_close($curl);
//        }
        return $data;
    }
}