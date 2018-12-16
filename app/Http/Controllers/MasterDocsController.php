<?php

namespace App\Http\Controllers;

use DB;
use Datatables;
use App\Models\MasterDocs;
use Illuminate\Http\Request;
use function Opis\Closure\serialize;

class MasterDocsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = DB::table('categories')->select('id', 'category_name')->get();
        return view('pages.master_docs.index', ['categories' => $categories]);
    }

    /**
     * Fetch JSON for Datatable
     *
     * @return JSON
     */
    public function JSONDatatable()
    {
        $masterDocs = MasterDocs::join('categories', 'master_docs.category_id', '=', 'categories.id')
            ->select(['master_docs.id', 'master_docs.title', 'categories.category_name', 'master_docs.created_by', 'master_docs.created_at']);

        return Datatables::of($masterDocs)
            ->addColumn('action', function ($doc) {
                return
                '<a href="' . route('all-documents.edit', $doc->id) . '" class="btn btn-sm btn-warning">
                    <i class="fa fa-lg fa-pencil"></i>
                </a>
                <a href="' . route('all-documents.destroy', $doc->id) . '" class="btn btn-sm btn-danger">
                    <i class="fa fa-lg fa-trash"></i>
                </a>';
            })
            ->editColumn('created_at', function ($doc) {
                if ($doc->created_at !== null) {
                    return date('d-m-Y / H:i', strtotime($doc->created_at));
                }

                return '-';
            })
            ->addIndexColumn()
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = DB::table('categories')->select('id', 'category_name')->get();
        return view('pages.master_docs.form', ['categories' => $categories]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 1. Validation
        $rules = [
            'category_id' => 'required|integer',
            'created_by'  => 'required|string|max:100',
            'title'       => 'required|string|unique:master_docs|max:100',
            'file'        => 'required|max:10000|mimes:pdf|file',
        ];

        $customMessages = [
            'required' => 'Kolom ini tidak boleh kosong.',
            'integer'  => 'Kolom ini hanya boleh diisi angka.',
            'unique'   => ':attribute telah digunakan.',
            'max'      => 'Panjang maksimal pada kolom ini adalah :max karakter.',
            'string'   => 'Kolom ini hanya boleh mengandung karakter atau angka.',
            'mimes'    => 'Dokumen yang diunggah harus memiliki ekstensi :values',
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
        $text    = $remover->remove($text);

        // 7. Stemming (Nazieb Andriani Algorithm)
        $stemmerFactory = new \Sastrawi\Stemmer\StemmerFactory();
        $stemmer        = $stemmerFactory->createStemmer();
        $text           = $stemmer->stem($text);

        // 8. Tokenizing (Convert each word to array)
        $output = $stemmer->stem($text);
        $tok = strtok($output, " \n\t");
        $output = [];
        while ($tok !== false) {
            $output[] = $tok;
            $tok = strtok(" \n\t");
        }

        // 9. Sorting Array Alphabetically
		sort($output, SORT_STRING);

        // 10. Serialized Array, so array can be inserted to MySQL (Serialized output is like JSON)
        $result = serialize($output);

        // 11. Saving to DB
        $masterDocs = new MasterDocs;
        $masterDocs->category_id = $request->category_id;
        $masterDocs->title       = $request->title;
        $masterDocs->created_by  = $request->created_by;
        $masterDocs->text        = $result;

        if ( $masterDocs->save() ) {
            $request->session()->flash('status', 'success');
            $request->session()->flash('message.title', 'Sukses!');
            $request->session()->flash('message.content', 'Dokumen berhasil ditambahkan!');
        } else {
            $request->session()->flash('status', 'danger');
            $request->session()->flash('message.title', 'Gagal...');
            $request->session()->flash('message.content', 'Terjadi kesalahan!');
        }

        return redirect()->route('all-documents.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\MasterDocs  $masterDocs
     * @return \Illuminate\Http\Response
     */
    public function show(MasterDocs $masterDocs)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\MasterDocs  $masterDocs
     * @return \Illuminate\Http\Response
     */
    public function edit(MasterDocs $masterDocs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\MasterDocs  $masterDocs
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MasterDocs $masterDocs)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\MasterDocs  $masterDocs
     * @return \Illuminate\Http\Response
     */
    public function destroy(MasterDocs $masterDocs)
    {
        //
    }
}
