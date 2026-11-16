<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\StaffIdCardTemplate;
use App\Services\ImageService;
use Illuminate\Http\Request;

class StaffIdCardTemplateController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    private $folder = 'uploads/staff_id_card_template';
    private $toggles = ['show_staff_name','show_staff_id','show_designation','show_department','show_father_name','show_mother_name','show_date_of_joining','show_current_address','show_phone','show_dob','show_qr_code','show_barcode'];

    public function index()
    {
        $templates = StaffIdCardTemplate::latest()->get();
        return view('admin.staff_id_card_template.index', compact('templates'));
    }

    public function edit($id)
    {
        $template = StaffIdCardTemplate::findOrFail($id);
        abort_unless(auth()->user()->can('access-id-card-design-' . $template->design), 403); // template's current design must be allowed
        return response()->json($template);
    }

    private function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'school_name' => 'required|string|max:255',
            'address_phone_email' => 'nullable|string|max:255',
            // old: 'header_color' => 'nullable|string|max:20',
            'header_color' => 'nullable|regex:/^#[0-9a-fA-F]{6}$/', // T338: value is printed into a <style> block
            'accent_color' => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'design_type' => 'required|in:horizontal,vertical',
            'design' => 'required|in:1,2,3',
            'background_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'signature' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function save(Request $request)
    {
        $request->validate($this->rules());
        abort_unless($request->user()->can('access-id-card-design-' . $request->design), 403); // each design is gated by its own permission
        $data = $request->only(['title','school_name','address_phone_email','header_color','accent_color','design_type','design']);

        foreach ($this->toggles as $toggle) {
            $data[$toggle] = $request->boolean($toggle);
        }

        if ($request->hasFile('background_image')) $data['background_image'] = $this->imageService->upload($request->file('background_image'), $this->folder, 600, 400);
        if ($request->hasFile('logo')) $data['logo'] = $this->imageService->upload($request->file('logo'), $this->folder, 200, 200);
        if ($request->hasFile('signature')) $data['signature'] = $this->imageService->upload($request->file('signature'), $this->folder, 300, 150);

        StaffIdCardTemplate::create($data);

        return redirect()->route('staff_id_card_template.index')->with('toastr-success', 'Staff ID Card Template created successfully.');
    }

    public function update(Request $request, $id)
    {
        $template = StaffIdCardTemplate::findOrFail($id);
        abort_unless($request->user()->can('access-id-card-design-' . $template->design), 403); // template's current design must be allowed
        $request->validate($this->rules());
        abort_unless($request->user()->can('access-id-card-design-' . $request->design), 403); // each design is gated by its own permission
        $data = $request->only(['title','school_name','address_phone_email','header_color','accent_color','design_type','design']);

        foreach ($this->toggles as $toggle) {
            $data[$toggle] = $request->boolean($toggle);
        }

        $oldFiles = []; // purani files naya save hone ke BAAD delete hongi
        if ($request->hasFile('background_image')) {
            $oldFiles[] = $template->background_image;
            $data['background_image'] = $this->imageService->upload($request->file('background_image'), $this->folder, 600, 400);
        }
        if ($request->hasFile('logo')) {
            $oldFiles[] = $template->logo;
            $data['logo'] = $this->imageService->upload($request->file('logo'), $this->folder, 200, 200);
        }
        if ($request->hasFile('signature')) {
            $oldFiles[] = $template->signature;
            $data['signature'] = $this->imageService->upload($request->file('signature'), $this->folder, 300, 150);
        }

        $template->update($data);

        foreach ($oldFiles as $oldFile) {
            $this->imageService->delete($oldFile, $this->folder);
        }

        return redirect()->route('staff_id_card_template.index')->with('toastr-success', 'Staff ID Card Template updated successfully.');
    }

    // T338: renders the real card view for ONE unsaved sample staff member (form live preview + eye button). Writes nothing to the DB.
    public function livePreview(Request $request)
    {
        $request->validate([
            'school_name' => 'nullable|string|max:255',
            'address_phone_email' => 'nullable|string|max:255',
            'header_color' => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'accent_color' => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'design_type' => 'required|in:horizontal,vertical',
            'design' => 'required|in:1,2,3',
            'template_id' => 'nullable|integer',
        ]);
        abort_unless($request->user()->can('access-id-card-design-' . $request->design), 403);

        $data = $request->only(['school_name', 'address_phone_email', 'header_color', 'accent_color', 'design_type', 'design']);
        foreach ($this->toggles as $toggle) {
            $data[$toggle] = $request->boolean($toggle);
        }
        $data['school_name'] = $data['school_name'] ?: 'Your School Name';

        $template = new StaffIdCardTemplate($data); // unsaved
        if ($request->filled('template_id') && ($saved = StaffIdCardTemplate::find($request->template_id))) {
            $template->logo = $saved->logo; // images are not part of the preview POST; reuse the saved ones
            $template->background_image = $saved->background_image;
        }

        // Unsaved sample teacher; role + profile relations are preset so staff_code / photo_url / getOrCreateQrCode() never query or insert
        $teacher = (new \App\Models\Teacher)->forceFill([
            'teacher_id' => 'TCH-0001', 'department' => 'Science', 'father_name' => 'Ahmed Khan', 'mother_name' => 'Sara Khan',
            'phone' => '0300-1234567', 'dob' => '1990-03-21', 'joining_date' => '2020-08-01', 'address' => 'House 12, Main Street, City',
        ]);
        $staff = (new \App\Models\User)->forceFill(['id' => 1, 'name' => 'Rao Imran']);
        $staff->setRelation('roles', collect([(new \Spatie\Permission\Models\Role)->forceFill(['name' => 'teacher'])]));
        $staff->setRelation('teacher', $teacher);
        $staff->setRelation('teacherWithTrashed', $teacher);
        $staff->setRelation('qrCode', (new \App\Models\QrCode)->forceFill(['code' => 'STF-DEMO-0001']));

        $staffMembers = collect([$staff]);
        $validSession = null;
        $isPreview = true;

        return view('admin.staff_id_card_template.card', compact('template', 'staffMembers', 'validSession', 'isPreview'));
    }

    public function delete($id)
    {
        $template = StaffIdCardTemplate::findOrFail($id);
        abort_unless(auth()->user()->can('access-id-card-design-' . $template->design), 403); // template's current design must be allowed
        $this->imageService->delete($template->background_image, $this->folder);
        $this->imageService->delete($template->logo, $this->folder);
        $this->imageService->delete($template->signature, $this->folder);
        $template->delete();

        return redirect()->route('staff_id_card_template.index')->with('toastr-success', 'Staff ID Card Template deleted successfully.');
    }
}
