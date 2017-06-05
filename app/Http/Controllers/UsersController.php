<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
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
        Auth::login($user);
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show', [$user]);
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
}
