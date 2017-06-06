@extends('layouts.default')
@section('title', '主页')
@section('content')
<div class="jumbotron">
   <h1>Hello World</h1>
   <p class="lead">
     欢迎光临我的个人博客
   </p>
   <p>
     一切，将从这里开始。
   </p>
   <p>
     <a class="btn btn-lg btn-success" href="{{ route('signup') }}" role="button">立即注册</a>
   </p>
 </div>
 @stop
