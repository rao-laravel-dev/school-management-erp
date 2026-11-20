<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\SchoolTiming;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        // T340a: header card counts (same "active" rules as the dashboard cards)
        $counts = [
            'students' => User::role('student')->where('status', 1)->count(),
            'staff'    => User::role(['teacher', 'receptionist', 'accountant', 'librarian'])->where('status', 1)->count(),
            'classes'  => SchoolClass::count(),
            'users'    => User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'teacher', 'staff', 'parent', 'student', 'receptionist', 'accountant', 'librarian']))
                ->where('status', 1)->withoutSuperadmin()->count(),
        ];
        $timing = SchoolTiming::active(); // "School at a glance" card
        $tab = in_array(request('tab'), ['profile', 'theme', 'checklist'], true) ? request('tab') : 'profile';

        /* old: return view('admin.site_setting.edit', compact('setting')); */
        return view('admin.site_setting.edit', compact('setting', 'counts', 'timing', 'tab'));
    }

    // T340a: save the Appearance & theme tab (own route; the profile form has other required fields)
    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme_palette' => ['required', Rule::in(array_merge(array_keys(SiteSetting::PALETTES), ['custom']))],
            'font_family'   => ['required', Rule::in(array_keys(SiteSetting::FONTS))],
            'sidebar_style' => ['required', Rule::in(array_keys(SiteSetting::SIDEBAR_STYLES))],
            'primary_color' => ['required_if:theme_palette,custom', 'nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color'  => ['required_if:theme_palette,custom', 'nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            // T340b: login page (column sizes: heading 150, message 255)
            'login_heading' => ['nullable', 'string', 'max:150'],
            'login_message' => ['nullable', 'string', 'max:255'],
            'login_background' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_login_background' => ['nullable', 'boolean'],
        ], [
            'primary_color.regex' => 'Primary colour must be a full hex value like #4b3bdb.',
            'accent_color.regex'  => 'Accent colour must be a full hex value like #ffb020.',
            'primary_color.required_if' => 'Pick a primary colour for the Custom palette.',
            'accent_color.required_if'  => 'Pick an accent colour for the Custom palette.',
        ]);

        $palette = $request->theme_palette;
        if ($palette === 'custom') {
            $primary = strtolower($request->primary_color);
            $accent  = strtolower($request->accent_color);
        } else {
            // named palette: colours come from the server list, posted colours are ignored
            [, $primary, $accent] = SiteSetting::PALETTES[$palette];
        }

        $setting = SiteSetting::current();
        $data = [
            'theme_palette' => $palette,
            'primary_color' => $primary,
            'accent_color'  => $accent,
            'font_family'   => $request->font_family,
            'sidebar_style' => $request->sidebar_style,
            'login_heading' => trim((string) $request->login_heading) ?: null, // T340b
            'login_message' => trim((string) $request->login_message) ?: null,
        ];

        // T340b: login background (same upload pattern as logo/icon); old file deleted after the save
        $oldFiles = [];
        if ($request->hasFile('login_background')) {
            $oldFiles[] = $setting->login_background;
            $data['login_background'] = $this->imageService->upload($request->file('login_background'), 'uploads/site_setting', 1600, 1200);
        } elseif ($request->boolean('remove_login_background')) {
            $oldFiles[] = $setting->login_background;
            $data['login_background'] = null;
        }

        /* old: SiteSetting::current()->update([ theme_palette, primary_color, accent_color, font_family, sidebar_style ]); */
        $setting->update($data);

        foreach ($oldFiles as $oldFile) {
            if ($oldFile) {
                $this->imageService->delete($oldFile, 'uploads/site_setting');
            }
        }

        return redirect()->route('site_setting.edit', ['tab' => 'theme'])->with('toastr-success', 'Theme saved. It applies to every user.');
    }

    public function resetTheme()
    {
        SiteSetting::current()->update(SiteSetting::THEME_DEFAULTS);

        return redirect()->route('site_setting.edit', ['tab' => 'theme'])->with('toastr-success', 'Theme reset to default.');
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
            'tagline' => 'nullable|string|max:150', // T340a
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'icon' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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
            'tagline',            // T340a
        ]);

        $oldFiles = []; // purani files naya save hone ke BAAD delete hongi
        if ($request->hasFile('logo')) {
            $oldFiles[] = $setting->logo;
            $data['logo'] = $this->imageService->upload($request->file('logo'), 'uploads/site_setting', 800, 300);
        }

        if ($request->hasFile('icon')) {
            $oldFiles[] = $setting->icon;
            $data['icon'] = $this->imageService->upload($request->file('icon'), 'uploads/site_setting', 512, 512);
        }

        if ($request->hasFile('principal_signature')) {
            $oldFiles[] = $setting->principal_signature;
            $data['principal_signature'] = $this->imageService->upload($request->file('principal_signature'), 'uploads/site_setting', 300, 150);
        }

        $setting->update($data);

        foreach ($oldFiles as $oldFile) {
            $this->imageService->delete($oldFile, 'uploads/site_setting');
        }

        return redirect()->route('site_setting.edit', ['tab' => 'profile'])->with('toastr-success', 'Site Settings updated successfully.');
    }
}
