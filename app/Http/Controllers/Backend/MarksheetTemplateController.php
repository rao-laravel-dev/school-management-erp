<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\MarksheetTemplate;
use App\Models\SiteSetting;
use App\Services\ImageService;
use App\Services\PrintDesignService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $designs = PrintDesignService::DESIGNS;
        $orientations = PrintDesignService::ORIENTATIONS;
        $site = SiteSetting::current();
        $missingBranding = PrintDesignService::missingBranding($site); // signature / head name Site Settings mein nahi
        $themePrimary = $site->theme['primary'];
        $themeAccent = $site->theme['accent'];
        return view('admin.marksheet_template.index', compact('templates', 'designs', 'orientations', 'missingBranding', 'themePrimary', 'themeAccent'));
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
            'design' => ['nullable', Rule::in(array_keys(PrintDesignService::DESIGNS))],
            'orientation' => ['nullable', Rule::in(array_keys(PrintDesignService::ORIENTATIONS))],
            'primary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }

    private function messages(): array
    {
        return [
            'design.in' => 'Selected Design is not available',
            'orientation.in' => 'Selected Orientation is not available',
            'primary_color.regex' => 'Main colour must be a valid colour like #0e3b78',
            'accent_color.regex' => 'Accent colour must be a valid colour like #ffb020',
        ];
    }

    // Design / orientation / colours: khali design = classic, khali orientation = portrait, khali colour = school theme colour
    private function designFields(Request $request): array
    {
        return [
            'design' => $request->input('design') ?: 'classic',
            'orientation' => $request->input('orientation') ?: 'portrait',
            'primary_color' => $request->input('primary_color') ?: null,
            'accent_color' => $request->input('accent_color') ?: null,
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
            'show_school_logo',
            'show_head_signature',
            'show_stamp',
            'show_subject_details',
            'show_summary_boxes',
            'show_grade_scale',
            'show_parent_sign',
        ];

        $data = [];
        foreach ($toggles as $toggle) {
            $data[$toggle] = $request->boolean($toggle);
        }

        return $data;
    }

    // Live preview: unsaved form values -> asli print blade with sample student + marks, DB mein kuch nahi likhta
    public function livePreview(Request $request)
    {
        $request->validate([
            'template_id' => ['nullable', 'integer', Rule::exists('marksheet_templates', 'id')],
            'design' => ['nullable', Rule::in(array_keys(PrintDesignService::DESIGNS))],
            'orientation' => ['nullable', Rule::in(array_keys(PrintDesignService::ORIENTATIONS))],
            'primary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'exam_name' => 'nullable|string|max:255',
            'exam_center' => 'nullable|string|max:255',
            'body_text' => 'nullable|string|max:5000',
            'footer_text' => 'nullable|string|max:5000',
            'printing_date' => 'nullable|date',
            'pending_images' => 'nullable|array',
            'pending_images.*' => ['string', Rule::in(array_keys($this->imageDimensions()))],
        ], $this->messages());

        $template = new MarksheetTemplate();
        $template->forceFill(array_merge(
            $request->only(['template_name', 'exam_name', 'school_name', 'exam_center', 'body_text', 'footer_text', 'printing_date']),
            $this->toggleFields($request),
            $this->designFields($request)
        ));

        // Saved template ki pehle se lagi images dikhao; abhi chuni hui (unsaved) image ke liye placeholder, browser usay replace karta hai
        $saved = $request->filled('template_id') ? MarksheetTemplate::find($request->template_id) : null;
        foreach (array_keys($this->imageDimensions()) as $field) {
            $template->{$field} = $saved ? $saved->{$field} : null;
        }
        foreach ((array) $request->input('pending_images', []) as $field) {
            $template->{$field} = '__pending__';
        }

        $sample = PrintDesignService::sampleMarksheet($template);
        $exam = $sample['exam'];
        $sheets = $sample['sheets'];
        $siteSetting = SiteSetting::current();
        $branding = PrintDesignService::branding($siteSetting, $template);
        $isPreview = true;

        return view('admin.print_marksheet.print', compact('exam', 'template', 'sheets', 'siteSetting', 'branding', 'isPreview'));
    } // End Method

    public function save(Request $request)
    {
        $request->validate($this->rules(), $this->messages());

        $data = $request->only([
            'template_name',
            'exam_name',
            'school_name',
            'exam_center',
            'body_text',
            'footer_text',
            'printing_date',
        ]);

        $data = array_merge($data, $this->toggleFields($request), $this->designFields($request));

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
        $request->validate($this->rules(), $this->messages());

        $data = $request->only([
            'template_name',
            'exam_name',
            'school_name',
            'exam_center',
            'body_text',
            'footer_text',
            'printing_date',
        ]);

        $data = array_merge($data, $this->toggleFields($request), $this->designFields($request));

        $oldFiles = []; // purani files naya save hone ke BAAD delete hongi
        foreach ($this->imageDimensions() as $field => [$width, $height]) {
            if ($request->hasFile($field)) {
                $oldFiles[] = $template->$field;
                $data[$field] = $this->imageService->upload($request->file($field), $this->folder, $width, $height);
            }
        }

        $template->update($data);

        foreach ($oldFiles as $oldFile) {
            $this->imageService->delete($oldFile, $this->folder);
        }

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
