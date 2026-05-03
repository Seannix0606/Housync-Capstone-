<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Services\Billing\PaymentVerificationImageStorageService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class LandlordInstapayQuickResponseCodeController extends Controller
{
    public function update(
        Request $request,
        PaymentVerificationImageStorageService $paymentVerificationImageStorage,
    ): RedirectResponse {
        $request->validate([
            'instapay_quick_response_code_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        try {
            $landlordUser = Auth::user();

            $previousStoredValue = $landlordUser->landlord_instapay_quick_response_code_image_path;

            $uploadedFile = $request->file('instapay_quick_response_code_image');
            $storedPathOrPublicUrl = $paymentVerificationImageStorage->persistInstapayCodeFromLandlord(
                $uploadedFile,
                (int) $landlordUser->id,
            );

            $landlordUser->landlord_instapay_quick_response_code_image_path = $storedPathOrPublicUrl;
            $landlordUser->save();

            // Only remove old local file after a successful DB update.
            $paymentVerificationImageStorage->deletePrivateDiskObjectIfPresent($previousStoredValue);

            return redirect()
                ->route('landlord.payments')
                ->with('success', 'InstaPay quick response code image updated. Tenants can view it when paying.');
        } catch (QueryException $queryException) {
            Log::error('Failed to save InstaPay quick response code (database)', [
                'message' => $queryException->getMessage(),
            ]);

            return redirect()
                ->route('landlord.payments')
                ->with('error', 'Failed to save InstaPay quick response code. Please run database migrations on production and try again.');
        } catch (Throwable $throwable) {
            Log::error('Failed to save InstaPay quick response code', [
                'message' => $throwable->getMessage(),
                'exception_class' => $throwable::class,
            ]);

            return redirect()
                ->route('landlord.payments')
                ->with('error', 'Failed to save InstaPay quick response code. Please try again.');
        }
    }

    public function destroy(PaymentVerificationImageStorageService $paymentVerificationImageStorage): RedirectResponse
    {
        try {
            $landlordUser = Auth::user();

            $paymentVerificationImageStorage->deletePrivateDiskObjectIfPresent(
                $landlordUser->landlord_instapay_quick_response_code_image_path,
            );

            $landlordUser->landlord_instapay_quick_response_code_image_path = null;
            $landlordUser->save();

            return redirect()
                ->route('landlord.payments')
                ->with('success', 'InstaPay quick response code image removed.');
        } catch (Throwable $throwable) {
            Log::error('Failed to remove InstaPay quick response code', [
                'message' => $throwable->getMessage(),
                'exception_class' => $throwable::class,
            ]);

            return redirect()
                ->route('landlord.payments')
                ->with('error', 'Failed to remove InstaPay quick response code. Please try again.');
        }
    }
}
