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
        return response()->json(StaffIdCardTemplate::findOrFail($id));
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
        $data = $request->only(['title','school_name','address_phone_email','header_color','design_type']);

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
        $request->validate($this->rules());
        $data = $request->only(['title','school_name','address_phone_email','header_color','design_type']);

        foreach ($this->toggles as $toggle) {
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

        return redirect()->route('staff_id_card_template.index')->with('toastr-success', 'Staff ID Card Template updated successfully.');
    }

    public function delete($id)
    {
        $template = StaffIdCardTemplate::findOrFail($id);
        $this->imageService->delete($template->background_image, $this->folder);
        $this->imageService->delete($template->logo, $this->folder);
        $this->imageService->delete($template->signature, $this->folder);
        $template->delete();

        return redirect()->route('staff_id_card_template.index')->with('toastr-success', 'Staff ID Card Template deleted successfully.');
    }
}
