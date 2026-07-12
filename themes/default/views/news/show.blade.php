@extends('theme::layouts.app')
@section('title',$news->title.' — '.config('app.name'))
@section('content')<section class="page-hero"><div class="container"><p class="eyebrow">{{ $news->published_at?->format('d.m.Y') }}</p><h1>{{ $news->title }}</h1></div></section><section class="container page-content"><article class="panel prose">{!! $news->body !!}</article></section>@endsection
