<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Services\Billing\PaymentVerificationImageStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandlordInstapayQuickResponseCodeController extends Controller
{
    public function update(
        Request $request,
        PaymentVerificationImageStorageService $paymentVerificationImageStorage,
    ): RedirectResponse {
        $request->validate([
            'instapay_quick_response_code_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $landlordUser = Auth::user();

        $previousStoredValue = $landlordUser->landlord_instapay_quick_response_code_image_path;
        $paymentVerificationImageStorage->deletePrivateDiskObjectIfPresent($previousStoredValue);

        $uploadedFile = $request->file('instapay_quick_response_code_image');
        $storedPathOrPublicUrl = $paymentVerificationImageStorage->persistInstapayCodeFromLandlord(
            $uploadedFile,
            (int) $landlordUser->id,
        );

        $landlordUser->landlord_instapay_quick_response_code_image_path = $storedPathOrPublicUrl;
        $landlordUser->save();

        return redirect()
            ->route('landlord.payments')
            ->with('success', 'InstaPay quick response code image updated. Tenants can view it when paying.');
    }

    public function destroy(PaymentVerificationImageStorageService $paymentVerificationImageStorage): RedirectResponse
    {
        $landlordUser = Auth::user();

        $paymentVerificationImageStorage->deletePrivateDiskObjectIfPresent(
            $landlordUser->landlord_instapay_quick_response_code_image_path,
        );

        $landlordUser->landlord_instapay_quick_response_code_image_path = null;
        $landlordUser->save();

        return redirect()
            ->route('landlord.payments')
            ->with('success', 'InstaPay quick response code image removed.');
    }
}
