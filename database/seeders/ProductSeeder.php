<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->produtos() as $dados) {
            [$p, $m, $g] = $dados['precos'];
            unset($dados['precos']);

            $product = Product::updateOrCreate(['slug' => $dados['slug']], $dados);

            $tamanhos = [
                ['key' => 'p', 'label' => 'Pequena', 'weight' => '120 g', 'burn_hours' => 25, 'price' => $p, 'position' => 1, 'stock' => 30],
                ['key' => 'm', 'label' => 'Média', 'weight' => '220 g', 'burn_hours' => 45, 'price' => $m, 'position' => 2, 'stock' => 25],
                ['key' => 'g', 'label' => 'Grande', 'weight' => '400 g', 'burn_hours' => 80, 'price' => $g, 'position' => 3, 'stock' => 12],
            ];

            foreach ($tamanhos as $tamanho) {
                $existente = $product->sizes()->where('key', $tamanho['key'])->first();
                if ($existente) {
                    $existente->update(collect($tamanho)->except('stock')->all());
                } else {
                    $product->sizes()->create($tamanho);
                }
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function produtos(): array
    {
        return [
            [
                'slug' => 'cumaru-baunilha',
                'name' => 'Cumaru & Baunilha',
                'collection' => 'casa',
                'family' => 'gourmand',
                'tagline' => 'Doce sem ser açucarado. A vela que a casa pede no domingo.',
                'description' => 'O cumaru, semente amazônica com cheiro de amêndoa e feno, encontra uma baunilha escura e pouco doce. O resultado é um aroma redondo, que preenche a sala sem pesar.',
                'notes_top' => 'Amêndoa, feno seco',
                'notes_heart' => 'Cumaru, fava tonka',
                'notes_base' => 'Baunilha bourbon, âmbar',
                'wax' => '#e9d8b8',
                'featured' => true,
                'precos' => [72, 124, 196],
            ],
            [
                'slug' => 'capim-limao',
                'name' => 'Capim-limão',
                'collection' => 'jardim',
                'family' => 'citrico',
                'tagline' => 'Verde, ácido, limpo. Cheiro de chá recém-coado no quintal.',
                'description' => 'Folhas de capim-limão amassadas na mão, com um fundo leve de gengibre. Uma vela para acender de manhã, enquanto a casa acorda.',
                'notes_top' => 'Capim-limão, lima',
                'notes_heart' => 'Gengibre, folha de figueira',
                'notes_base' => 'Musgo claro',
                'wax' => '#e4e8c8',
                'featured' => true,
                'precos' => [66, 114, 182],
            ],
            [
                'slug' => 'flor-de-laranjeira',
                'name' => 'Flor de Laranjeira',
                'collection' => 'jardim',
                'family' => 'floral',
                'tagline' => 'A flor, não a fruta. Branca, leve, com um pouco de mel.',
                'description' => 'Néroli e pétalas de laranjeira, com um toque de mel de flor de laranja no fundo. Delicada o suficiente para o quarto, presente o suficiente para a sala.',
                'notes_top' => 'Néroli, bergamota',
                'notes_heart' => 'Flor de laranjeira, jasmim',
                'notes_base' => 'Mel, almíscar branco',
                'wax' => '#f1e6d0',
                'featured' => false,
                'precos' => [74, 128, 204],
            ],
            [
                'slug' => 'cafe-cacau',
                'name' => 'Café & Cacau',
                'collection' => 'casa',
                'family' => 'gourmand',
                'tagline' => 'Grão torrado e cacau amargo. Para a mesa de trabalho.',
                'description' => 'Café moído na hora e nibs de cacau, sem o doce da confeitaria. Um aroma seco e quente que combina com livros abertos e tarde de chuva.',
                'notes_top' => 'Grão de café torrado',
                'notes_heart' => 'Cacau amargo, cardamomo',
                'notes_base' => 'Madeira de carvalho',
                'wax' => '#b98f6e',
                'featured' => false,
                'precos' => [72, 124, 196],
            ],
            [
                'slug' => 'alecrim-sal',
                'name' => 'Alecrim & Sal',
                'collection' => 'jardim',
                'family' => 'herbal',
                'tagline' => 'Herbal e mineral, como uma horta perto do mar.',
                'description' => 'Alecrim fresco quebrado no talo, com sal marinho e um fundo discreto de eucalipto. Uma vela que deixa o ar mais leve, boa para a cozinha e o banho.',
                'notes_top' => 'Alecrim, eucalipto',
                'notes_heart' => 'Sal marinho, sálvia',
                'notes_base' => 'Vetiver claro',
                'wax' => '#d9dfcd',
                'featured' => true,
                'precos' => [66, 114, 182],
            ],
            [
                'slug' => 'jasmim-noturno',
                'name' => 'Jasmim Noturno',
                'collection' => 'noite',
                'family' => 'floral',
                'tagline' => 'O jasmim que só abre depois que o sol vai embora.',
                'description' => 'Jasmim-manga e dama-da-noite, flores que perfumam ruas inteiras à noite, sobre um fundo cremoso de sândalo. Intensa, para quem gosta de floral com corpo.',
                'notes_top' => 'Folha verde, pera',
                'notes_heart' => 'Jasmim-manga, dama-da-noite',
                'notes_base' => 'Sândalo, baunilha',
                'wax' => '#e8dde0',
                'featured' => false,
                'precos' => [78, 134, 212],
            ],
            [
                'slug' => 'cedro-fumaca',
                'name' => 'Cedro & Fumaça',
                'collection' => 'noite',
                'family' => 'amadeirado',
                'tagline' => 'Lenha, resina e o resto de uma fogueira.',
                'description' => 'Cedro seco, um fio de fumaça e resina de benjoim. É a vela mais escura da casa, para noites frias e conversas longas.',
                'notes_top' => 'Pimenta rosa, cedro',
                'notes_heart' => 'Fumaça, couro',
                'notes_base' => 'Benjoim, patchouli',
                'wax' => '#c9b79b',
                'featured' => true,
                'precos' => [78, 134, 212],
            ],
            [
                'slug' => 'pitanga',
                'name' => 'Pitanga',
                'collection' => 'casa',
                'family' => 'citrico',
                'tagline' => 'Fruta de quintal, agridoce, com a folha junto.',
                'description' => 'A pitanga madura, com a acidez e o cheiro verde da folha esmagada. Alegre e um pouco nostálgica, para deixar a casa com cheiro de infância.',
                'notes_top' => 'Pitanga, tangerina',
                'notes_heart' => 'Folha de pitangueira, pimenta',
                'notes_base' => 'Cedro, almíscar',
                'wax' => '#e7c7b8',
                'featured' => false,
                'precos' => [66, 114, 182],
            ],
            [
                'slug' => 'figo-vetiver',
                'name' => 'Figo & Vetiver',
                'collection' => 'noite',
                'family' => 'amadeirado',
                'tagline' => 'Figo verde, leite da folha e raiz de vetiver.',
                'description' => 'Um figo ainda verde, com o leite da folha, sobre vetiver terroso. Equilibrada entre o fresco e o profundo, é a vela mais pedida para presente.',
                'notes_top' => 'Figo verde, folha',
                'notes_heart' => 'Leite de coco, íris',
                'notes_base' => 'Vetiver, cedro',
                'wax' => '#cfc7a6',
                'featured' => false,
                'precos' => [78, 134, 212],
            ],
        ];
    }
}
