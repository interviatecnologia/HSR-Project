<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use App\Models\Calllog; 
use App\Models\Recording; 

class RecordingController extends Controller
{
    // Listar gravações
    public function listRecordings()
    {
        $recordingPath = '/var/spool/asterisk/monitorDONE/MP3';
        if (!File::isDirectory($recordingPath)) {
            return response()->json(['error' => 'Directory not found'], 404);
        }

        $files = File::files($recordingPath);
        $recordings = [];
        foreach ($files as $file) {
            $recordings[] = $file->getFilename();
        }

        return response()->json($recordings, 200);
    }

    // Baixar gravação
    public function downloadRecording($filename)
    {
        $filePath = '/var/spool/asterisk/monitorDONE/MP3/' . $filename;

        if (!File::exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->download($filePath);
    }

    // Reproduzir gravação
    public function playRecording($filename)
    {
        $filePath = '/var/spool/asterisk/monitorDONE/MP3/' . $filename;

        if (!File::exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $fileContents = File::get($filePath);
        $response = Response::make($fileContents, 200);
        $response->header('Content-Type', 'audio/mpeg');

        return $response;
    }

     // Buscar gravações com filtros
public function searchRecordings(Request $request)
{
    $recordingPath = '/var/spool/asterisk/monitorDONE/MP3';
    if (!File::isDirectory($recordingPath)) {
        return response()->json(['error' => 'Directory not found'], 404);
    }

    $date = $request->input('date');
    $phoneNumber = $request->input('phone_number');
    $agent = $request->input('agent');

    // Filtrar gravações do diretório
    $files = File::files($recordingPath);
    $filteredRecordings = [];
    foreach ($files as $file) {
        $filename = $file->getFilename();
        $parts = explode('_', $filename);

        if (count($parts) < 3) {
            continue;
        }

        $fileDate = substr($parts[0], 0, 8); // Data no formato YYYYMMDD
        $filePhoneNumber = substr($parts[0], 9); // Número de telefone
        $fileAgent = $parts[2]; // Agente

        if (
            (!$date || strpos($fileDate, $date) !== false) &&
            (!$phoneNumber || strpos($filePhoneNumber, $phoneNumber) !== false) &&
            (!$agent || strpos($fileAgent, $agent) !== false)
        ) {
            // Adicionar o nome do arquivo à lista de gravações filtradas
            $filteredRecordings[] = [
                'filename' => $filename,
                'uniqued' => null // Inicializa como null
            ];

            // Agora, buscar o 'uniqued' na tabela call_log
            $dialedNumber = substr($filePhoneNumber, 2); // Remove o código do país
            $callLog = CallLog::where('extension', $agent) // Use o agente aqui
                ->where('user', $request->input('user')) // Certifique-se de que 'user' está no request
                ->where('start_time', '>=', $request->input('start_time')) // Certifique-se de que 'start_time' está no request
                ->where('end_time', '<=', $request->input('end_time')) // Certifique-se de que 'end_time' está no request
                ->where('number_dialed', $dialedNumber)
                ->first();

            if ($callLog) {
                // Atualiza o último registro filtrado com o 'uniqued'
                $filteredRecordings[count($filteredRecordings) - 1]['uniqued'] = $callLog->uniqued;
            }
        }
    }

    return response()->json($filteredRecordings, 200);
}

}