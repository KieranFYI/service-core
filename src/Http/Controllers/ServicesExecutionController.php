<?php

namespace KieranFYI\Services\Core\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use KieranFYI\Services\Core\Models\Service;
use KieranFYI\Services\Core\Models\ServiceModelType;
use Laravie\SerializesQuery\Eloquent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ServicesExecutionController extends Controller
{

    public function execute(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'query' => ['required', 'array'],
                'columns' => ['required', 'array'],
                'model' => ['required', 'string']
            ]);

            /** @var Service $service */
            $service = Auth::user();
            $type = ServiceModelType::firstOrCreate(['name' => $validated['model']]);

            /** @var ServiceModelType $serviceType */
            $serviceType = $service->types->firstWhere('name', $type->name);
            if (is_null($serviceType)) {
                $service->types()->syncWithPivotValues($type, ['last_used_at' => Carbon::now()], false);
                abort(403);
            } else if (!$serviceType->pivot->accessible) {
                $serviceType->update(['last_used_at' => Carbon::now()]);
                abort(403);
            }
            $serviceType->update(['last_used_at' => Carbon::now()]);

            $query = Eloquent::unserialize([
                'model' => [
                    'class' => $validated['model'],
                    'connection' => config('database.default'),
                    'removedScopes' => [],
                    'eager' => [],
                ],
                'builder' => $validated['query']
            ]);

            return response()->json($query->get($validated['columns']));
        } catch (HttpException $e) {
            throw $e;
        } catch (Exception $e) {
            abort(501);
        }
    }
}