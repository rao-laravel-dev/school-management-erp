<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\IdCardTemplate;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Services\ImageService;
use Illuminate\Http\Request;

class IdCardTemplateController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    private $folder = 'uploads/id_card_template';

    public function index()
    {
        $templates = IdCardTemplate::latest()->get();
        return view('admin.id_card_template.index', compact('templates'));
    }

    public function edit($id)
    {
        $template = IdCardTemplate::findOrFail($id);
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

        $data = $request->only([
            'title', 'school_name', 'address_phone_email', 'header_color', 'accent_color', 'design_type', 'design',
            'show_admission_no', 'show_student_name', 'show_class', 'show_father_name',
            'show_mother_name', 'show_address', 'show_phone', 'show_dob',
            'show_blood_group', 'show_roll_no', 'show_house', 'show_qr_code', 'show_barcode',
        ]);

        foreach (['show_admission_no','show_student_name','show_class','show_father_name','show_mother_name','show_address','show_phone','show_dob','show_blood_group','show_roll_no','show_house','show_qr_code','show_barcode'] as $toggle) {
            $data[$toggle] = $request->boolean($toggle);
        }

        if ($request->hasFile('background_image')) {
            $data['background_image'] = $this->imageService->upload($request->file('background_image'), $this->folder, 600, 400);
        }
        if ($request->hasFile('logo')) {
            $data['logo'] = $this->imageService->upload($request->file('logo'), $this->folder, 200, 200);
        }
        if ($request->hasFile('signature')) {
            $data['signature'] = $this->imageService->upload($request->file('signature'), $this->folder, 300, 150);
        }

        IdCardTemplate::create($data);

        return redirect()->route('id_card_template.index')->with('toastr-success', 'ID Card Template created successfully.');
    }

    public function update(Request $request, $id)
    {
        $template = IdCardTemplate::findOrFail($id);
        abort_unless($request->user()->can('access-id-card-design-' . $template->design), 403); // template's current design must be allowed
        $request->validate($this->rules());
        abort_unless($request->user()->can('access-id-card-design-' . $request->design), 403); // each design is gated by its own permission

        $data = $request->only([
            'title', 'school_name', 'address_phone_email', 'header_color', 'accent_color', 'design_type', 'design',
        ]);

        foreach (['show_admission_no','show_student_name','show_class','show_father_name','show_mother_name','show_address','show_phone','show_dob','show_blood_group','show_roll_no','show_house','show_qr_code','show_barcode'] as $toggle) {
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

        return redirect()->route('id_card_template.index')->with('toastr-success', 'ID Card Template updated successfully.');
    }

    // T338: renders the real card view for ONE unsaved sample student (form live preview + eye button). Writes nothing to the DB.
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

        $toggles = ['show_admission_no','show_student_name','show_class','show_father_name','show_mother_name','show_address','show_phone','show_dob','show_blood_group','show_roll_no','show_house','show_qr_code','show_barcode'];
        $data = $request->only(['school_name', 'address_phone_email', 'header_color', 'accent_color', 'design_type', 'design']);
        foreach ($toggles as $toggle) {
            $data[$toggle] = $request->boolean($toggle);
        }
        $data['school_name'] = $data['school_name'] ?: 'Your School Name';

        $template = new IdCardTemplate($data); // unsaved
        if ($request->filled('template_id') && ($saved = IdCardTemplate::find($request->template_id))) {
            $template->logo = $saved->logo; // images are not part of the preview POST; reuse the saved ones
            $template->background_image = $saved->background_image;
        }

        // Unsaved sample student; qrCode relation is preset so getOrCreateQrCode() never inserts
        $student = (new \App\Models\Student)->forceFill([
            'id' => 1, 'first_name' => 'Rao', 'last_name' => 'Imran', 'admission_no' => 'ADM-0001',
            'date_of_birth' => '2012-05-14', 'blood_group' => 'B+',
        ]);
        $student->setRelation('parent', (new \App\Models\ParentProfile)->forceFill([
            'father_name' => 'Ahmed Khan', 'mother_name' => 'Sara Khan', 'father_phone' => '0300-1234567',
            'guardian_address' => 'House 12, Main Street, City',
        ]));
        $student->setRelation('house', (new \App\Models\StudentHouse)->forceFill(['name' => 'Blue House']));
        $student->setRelation('qrCode', (new \App\Models\QrCode)->forceFill(['code' => 'STU-DEMO-0001']));

        $enrollment = (new Enrollment)->forceFill(['roll_no' => '12']);
        $enrollment->setRelation('student', $student);
        $enrollment->setRelation('schoolClass', (new SchoolClass)->forceFill(['name' => 'Class 5']));
        $enrollment->setRelation('section', (new Section)->forceFill(['name' => 'A']));

        $enrollments = collect([$enrollment]);
        $validSession = null;
        $isPreview = true;

        return view('admin.id_card_template.card', compact('template', 'enrollments', 'validSession', 'isPreview'));
    }

    public function delete($id)
    {
        $template = IdCardTemplate::findOrFail($id);
        abort_unless(auth()->user()->can('access-id-card-design-' . $template->design), 403); // template's current design must be allowed
        $this->imageService->delete($template->background_image, $this->folder);
        $this->imageService->delete($template->logo, $this->folder);
        $this->imageService->delete($template->signature, $this->folder);
        $template->delete();

        return redirect()->route('id_card_template.index')->with('toastr-success', 'ID Card Template deleted successfully.');
    }
}
