<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AiTaskProposalService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AiTaskProposalController extends Controller
{
    public function store(
        Request $request,
        AiTaskProposalService $ai
    ): JsonResponse {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        try {
            $proposal = $ai->propose($validated['prompt']);
        } catch (ConnectionException | RequestException $exception) {
            Log::warning('Ollama request failed.', [
                'exception' => $exception::class,
            ]);

            return response()->json([
                'message' => 'The local AI service is unavailable. Please try again.',
            ], 503);
        } catch (RuntimeException $exception) {
            Log::warning('Ollama returned an unusable proposal.', [
                'exception' => $exception::class,
            ]);

            return response()->json([
                'message' => 'The AI could not create a valid proposal. Please try again.',
            ], 502);
        }

        return response()->json([
            'message' => 'Review the proposed task before saving.',
            'proposal' => $proposal,
        ]);
    }
}
