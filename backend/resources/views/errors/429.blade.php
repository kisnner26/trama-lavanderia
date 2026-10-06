@extends('errors.layout')
@section('error-code', '429')
@section('error-heading', 'un momento entre intentos.')
@section('error-message', 'recibimos demasiados intentos seguidos. espera un momento y vuelve a probar.')
