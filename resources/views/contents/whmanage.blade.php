@extends('layouts.master')
@section('title', 'Home')
@section('content')
<table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Name</th>
      <th scope="col">Email</th>
    </tr>
  </thead>
  <tbody>
    @foreach($users as $user)
      <tr>
        <td> {{$user->name}} </td>
        <td> {{$user->email}} </td>
      </tr>
    @endforeach
  </tbody>
</table>
@endsection
