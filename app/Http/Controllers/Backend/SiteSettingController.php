<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\ImageService;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function edit()
    {
        $setting = SiteSetting::current();
        return view('admin.site_setting.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|regex:/^[0-9+\-\s()]+$/|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'registration_no' => 'nullable|string|max:100',
            'admission_prefix' => 'required|string|max:10|alpha_num', // 👈 NAYA
            'established_year' => 'nullable|digits:4|max:10',
            'header_note' => 'nullable|string|max:255',
            'footer_note' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'principal_signature' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'phone.regex' => 'Phone number format is invalid.',
            'website.url' => 'Website must be a valid URL (e.g. https://example.com).',
            'established_year.digits' => 'Established Year must be a 4-digit year (e.g. 2010).',
            'admission_prefix.required' => 'Admission Prefix is required (e.g. APS, SSS).',
            'admission_prefix.alpha_num' => 'Admission Prefix should only contain letters and numbers (no spaces or dashes).',
        ]);

        $setting = SiteSetting::current();

        $data = $request->only([
            'school_name',
            'address',
            'phone',
            'email',
            'website',
            'registration_no',
            'admission_prefix',   // 👈 NAYA
            'established_year',
            'header_note',
            'footer_note',
        ]);

        if ($request->hasFile('logo')) {
            $this->imageService->delete($setting->logo, 'uploads/site_setting');
            $data['logo'] = $this->imageService->upload($request->file('logo'), 'uploads/site_setting', 400, 150);
        }

        if ($request->hasFile('principal_signature')) {
            $this->imageService->delete($setting->principal_signature, 'uploads/site_setting');
            $data['principal_signature'] = $this->imageService->upload($request->file('principal_signature'), 'uploads/site_setting', 300, 150);
        }

        $setting->update($data);

        return redirect()->route('site_setting.edit')->with('toastr-success', 'Site Settings updated successfully.');
    }
}
