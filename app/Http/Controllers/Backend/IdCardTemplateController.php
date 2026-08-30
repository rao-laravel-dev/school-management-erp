<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\IdCardTemplate;
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
        return response()->json($template);
    }

    private function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'school_name' => 'required|string|max:255',
            'address_phone_email' => 'nullable|string|max:255',
            'header_color' => 'nullable|string|max:20',
            'design_type' => 'required|in:horizontal,vertical',
            'background_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'signature' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function save(Request $request)
    {
        $request->validate($this->rules());

        $data = $request->only([
            'title', 'school_name', 'address_phone_email', 'header_color', 'design_type',
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
        $request->validate($this->rules());

        $data = $request->only([
            'title', 'school_name', 'address_phone_email', 'header_color', 'design_type',
        ]);

        foreach (['show_admission_no','show_student_name','show_class','show_father_name','show_mother_name','show_address','show_phone','show_dob','show_blood_group','show_roll_no','show_house','show_qr_code','show_barcode'] as $toggle) {
            $data[$toggle] = $request->boolean($toggle);
        }

        if ($request->hasFile('background_image')) {
            $this->imageService->delete($template->background_image, $this->folder);
            $data['background_image'] = $this->imageService->upload($request->file('background_image'), $this->folder, 600, 400);
        }
        if ($request->hasFile('logo')) {
            $this->imageService->delete($template->logo, $this->folder);
            $data['logo'] = $this->imageService->upload($request->file('logo'), $this->folder, 200, 200);
        }
        if ($request->hasFile('signature')) {
            $this->imageService->delete($template->signature, $this->folder);
            $data['signature'] = $this->imageService->upload($request->file('signature'), $this->folder, 300, 150);
        }

        $template->update($data);

        return redirect()->route('id_card_template.index')->with('toastr-success', 'ID Card Template updated successfully.');
    }

    public function delete($id)
    {
        $template = IdCardTemplate::findOrFail($id);
        $this->imageService->delete($template->background_image, $this->folder);
        $this->imageService->delete($template->logo, $this->folder);
        $this->imageService->delete($template->signature, $this->folder);
        $template->delete();

        return redirect()->route('id_card_template.index')->with('toastr-success', 'ID Card Template deleted successfully.');
    }
}
