<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Mail;
class UsersController extends Controller
{
  //防止未登录用户可以访问 edit 和 update 动作；
    function __construct(){
      $this->middleware('auth', [
            'only' => ['edit', 'update', 'destroy']
        ]);
      $this->middleware('guest', [
              'only' => ['create']
        ]);
    }
    //用户列表
    function index(){
      $users = User::paginate(15);
      return view('users.index', compact('users'));
    }
  //用户注册页面
    function create(){
      return view('users.create');
    }
    //个人信息页面
    function show($id){
      $user = User::findOrFail($id);
      return view('users.show',compact('user'));
    }
    //接受注册方法
    function store(Request $request){
      $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required'
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        //Auth::login($user);
        $this->sendEmailConfirmationTo($user);
         session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
         return redirect('/');
    }
    //信息修改
    function edit($id){
      $user = User::findOrFail($id);
      $this->authorize('update', $user);
      return view('users.edit',compact('user'));
    }
    //接受信息
    function update($id,Request $request){
        $this->validate($request,[
          'name' => 'required|max:50',
          'password' => 'required|confirmed|min:6'
        ]);
        $user = User::findOrFail($id);
        $this->authorize('update', $user);
        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $id);
    }
    //删除用户
    public function destroy($id){
              $user = User::findOrFail($id);
              $this->authorize('destroy', $user);
              $user->delete();
              session()->flash('success', '成功删除用户！');
              return back();
    }
    //发送邮件
    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'aufree@estgroupe.com';
        $name = 'Aufree';
        $to = $user->email;
        $subject = "感谢注册 Sample 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }
    //激活邮件
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }
}
