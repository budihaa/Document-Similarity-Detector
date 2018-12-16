<?php

namespace App\Http\Controllers;

use DB;
use App\Models\MasterDocs;
use Illuminate\Http\Request;
use function Opis\Closure\unserialize;

use TextAnalysis\Comparisons\CosineSimilarityComparison;

class DetectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $categories = DB::table('categories')->select('id', 'category_name')->get();
        $documents = DB::table('master_docs')->select('id', 'title', 'created_by')->get();

        return view('pages.detect.index', ['categories' => $categories, 'documents' => $documents]);
    }

    public function getMasterDocs(Request $request)
    {
        $category_id = $request->input('category_id');

        $categories = DB::table('master_docs')
            ->select('id', 'title', 'created_by')
            ->where('category_id', $category_id)
            ->get();

        return $categories->toJson();
    }

    public function upload(Request $request)
    {
        // ===== I. Tahap 1: Text-Processing dokumen yang di upload =====
        // 1. Validating File
        $rules = ['file' => 'required|max:10000|mimes:pdf'];

        $customMessages = [
            'required' => 'Dokumen tidak boleh kosong',
            'max' => 'Upload file maksimal 10 MB',
            'mimes' => 'Dokumen harus berupa PDF'
        ];

        $this->validate($request, $rules, $customMessages);

        // 2. Uploading File
        $uploadedFile = $request->file('file');

        // 3. Parse PDF to text
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($uploadedFile);
        $text = $pdf->getText();

        // 4. Remove all extra white spaces (kalo masih ada spasi yang lebih dari 1 proses filtering & stemming bakal ga tepat)
        $text = trim(preg_replace('/\s+/', ' ', $text));

        // 5. Case Folding
        $text = strtolower($text);

        // 6. Filtering (Remove Stopwords)
        $factory = new \Sastrawi\StopWordRemover\StopWordRemoverFactory();
        $remover = $factory->createStopWordRemover();
        $text = $remover->remove($text);

        // 7. Stemming (with Nazieb Andriani Algorithm)
        $stemmerFactory = new \Sastrawi\Stemmer\StemmerFactory();
        $stemmer = $stemmerFactory->createStemmer();

        // 8. Tokenizing (Convert text to array)
        $output = $stemmer->stem($text);
        $tok = strtok($output, " \n\t");
        $docQuery = [];
        while ($tok !== false) {
            $docQuery[] = $tok;
            $tok = strtok(" \n\t");
        }

		// 9. Sorting Alphabet
		sort($docQuery, SORT_STRING);

		// 10. Change to common text
		// $docQuery = implode(' ', $docQuery);

        // ====== II. Tahap 2: Ambil teks dokumen pembanding =====
        $masterDocs = MasterDocs::findOrFail($request->master_doc_id);

		$samples = [];
		foreach ($masterDocs as $master) {
			$samples[] = unserialize($master->text);
		}

		var_dump($docQuery, $samples);

		// $id[] = 'query';
		// $samples[] = $docQuery;

		$compare = new CosineSimilarityComparison();
		foreach ($samples as $key => $value) {
			$cosine = $compare->similarity($docQuery, $value);
			var_dump($cosine);
		}

        // return view('pages.detect.table', $params);
    }
}
