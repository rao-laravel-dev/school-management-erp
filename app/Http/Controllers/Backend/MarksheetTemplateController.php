<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\MarksheetTemplate;
use App\Services\ImageService;
use Illuminate\Http\Request;

class MarksheetTemplateController extends Controller
{
    protected $imageService;
    private $folder = 'uploads/marksheet_template';

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index()
    {
        $templates = MarksheetTemplate::latest()->get();
        return view('admin.marksheet_template.index', compact('templates'));
    }

    public function edit($id)
    {
        $template = MarksheetTemplate::findOrFail($id);
        return response()->json($template);
    }

    private function rules()
    {
        return [
            'template_name' => 'required|string|max:255',
            'exam_name' => 'required|string|max:255',
            'school_name' => 'nullable|string|max:255',
            'exam_center' => 'required|string|max:255',
            'body_text' => 'required|string',
            'footer_text' => 'required|string',
            'printing_date' => 'nullable|date',
            'header_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'left_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'right_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'left_sign' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'middle_sign' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'right_sign' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'background_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    // field => [width, height] — dimension per image type
    private function imageDimensions(): array
    {
        return [
            'header_image' => [1200, 150],
            'left_logo' => [200, 200],
            'right_logo' => [200, 200],
            'left_sign' => [300, 150],
            'middle_sign' => [300, 150],
            'right_sign' => [300, 150],
            'background_image' => [1654, 2339],
        ];
    }

    private function toggleFields(Request $request): array
    {
        $toggles = [
            'show_name',
            'show_father_name',
            'show_mother_name',
            'show_exam_session',
            'show_admission_no',
            'show_division',
            'show_rank',
            'show_roll_number',
            'show_photo',
            'show_class',
            'show_section',
            'show_dob',
            'show_remark',
        ];

        $data = [];
        foreach ($toggles as $toggle) {
            $data[$toggle] = $request->boolean($toggle);
        }

        return $data;
    }

    public function save(Request $request)
    {
        $request->validate($this->rules());

        $data = $request->only([
            'template_name',
            'exam_name',
            'school_name',
            'exam_center',
            'body_text',
            'footer_text',
            'printing_date',
        ]);

        $data = array_merge($data, $this->toggleFields($request));

        foreach ($this->imageDimensions() as $field => [$width, $height]) {
            if ($request->hasFile($field)) {
                $data[$field] = $this->imageService->upload($request->file($field), $this->folder, $width, $height);
            }
        }

        MarksheetTemplate::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Marksheet template created successfully.',
        ]);
    }

    public function update(Request $request, $id)
    {
        $template = MarksheetTemplate::findOrFail($id);
        $request->validate($this->rules());

        $data = $request->only([
            'template_name',
            'exam_name',
            'school_name',
            'exam_center',
            'body_text',
            'footer_text',
            'printing_date',
        ]);

        $data = array_merge($data, $this->toggleFields($request));

        foreach ($this->imageDimensions() as $field => [$width, $height]) {
            if ($request->hasFile($field)) {
                $this->imageService->delete($template->$field, $this->folder);
                $data[$field] = $this->imageService->upload($request->file($field), $this->folder, $width, $height);
            }
        }

        $template->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Marksheet template updated successfully.',
        ]);
    }

    public function destroy($id)
    {
        $template = MarksheetTemplate::findOrFail($id);

        foreach (array_keys($this->imageDimensions()) as $field) {
            $this->imageService->delete($template->$field, $this->folder);
        }

        $template->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Marksheet template deleted successfully.',
        ]);
    }
}
