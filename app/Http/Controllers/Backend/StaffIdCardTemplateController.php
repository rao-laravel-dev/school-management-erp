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
            'header_color' => 'nullable|string|max:20',
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
        $data = $request->only(['title','school_name','address_phone_email','header_color','design_type','design']);

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
        $data = $request->only(['title','school_name','address_phone_email','header_color','design_type','design']);

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
