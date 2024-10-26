<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class AudioController extends Controller
{
    private static $validatorFields = [
       'format' => 'string|in:mp3,wav'
    ];

    public function get(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), self::$validatorFields);
        if($validator->fails())
            return response()->json($validator->errors(), 400);

        $format = $request->input('format', 'mp3');
        $path = ""; // obtem o caminho do arquivo;
        
        if (!file_exists($path))
            return response()->json('Not Found', 404);
        
        $acceptsAudio = strpos($_SERVER['HTTP_ACCEPT'], 'audio/') !== false;
        if (!$acceptsAudio)
            return response()->download($path);

        $size = filesize($path);
        $length = $size;
        $start = 0;
        $end = $size - 1;
    
        // Verificar se há um cabeçalho Range
        if (isset($_SERVER['HTTP_RANGE'])) {
            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            $range = explode('-', $range);
    
            $start = intval($range[0]);
            $end = isset($range[1]) ? intval($range[1]) : $end;
    
            if ($start >= $size || $end >= $size || $start > $end) {
                return response()->json(['error' => 'Requested range not satisfiable'], 416);
            }
    
            $length = $end - $start + 1;
            header('HTTP/1.1 206 Partial Content');
        }
    
        // Definir os cabeçalhos de resposta
        $headers = [
            'Content-Type' => self::getAudioContentType($format),
            'Content-Length' => $length,
            'Accept-Ranges' => 'bytes',
            'Content-Range' => 'bytes ' . $start . '-' . $end . '/' . $size,
        ];
    
        $fp = fopen($path, 'rb');
        fseek($fp, $start);
        $chunkSize = 1024 * 8; // 8KB de chunk
    
        return Response::stream(function () use ($fp, $chunkSize, $end) {
            while (!feof($fp) && ftell($fp) <= $end) {
                echo fread($fp, $chunkSize);
                flush();
            }
            fclose($fp);
        }, 200, $headers);
    }

    private static function getAudioContentType(string $format)
    {
        switch (Str::upper($format)) {
            case 'MP3':
                return 'audio/mpeg';
            case 'WAV':
                return 'audio/wav';
            case 'OGG':
                return 'audio/ogg';
            case 'AAC':
                return 'audio/aac';
            case 'FLAC':
                return 'audio/flac';
            case 'M4A':
                return 'audio/mp4';
            case 'WMA':
                return 'audio/x-ms-wma';
            case 'AIFF':
                return 'audio/aiff';
            case 'AMR':
                return 'audio/amr';
            case 'MIDI':
                return 'audio/midi';
            case 'OPUS':
                return 'audio/opus';
            default:
                return '';
        }
    }
}
