<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            // Design e Criatividade
            'Design Gráfico',
            'Design de Logos',
            'Design UI/UX',
            'Ilustração',
            'Animação 2D/3D',
            'Modelagem 3D',
            'Fotografia',
            'Edição de Fotos',
            'Edição de Vídeo',
            'Produção de Vídeo',
            'Motion Graphics',

            // Desenvolvimento e Tecnologia
            'Desenvolvimento Web',
            'Desenvolvimento Mobile',
            'Programação Front-end',
            'Programação Back-end',
            'Desenvolvimento Full Stack',
            'Desenvolvimento de Jogos',
            'Administração de Banco de Dados',
            'Segurança da Informação',
            'DevOps',
            'Teste e QA',
            'Automação de Processos',
            'Inteligência Artificial e Machine Learning',

            // Marketing e Vendas
            'Marketing Digital',
            'SEO - Otimização para Buscadores',
            'Gestão de Mídias Sociais',
            'Publicidade Online',
            'Copywriting',
            'Email Marketing',
            'Gerenciamento de Projetos',
            'Branding',
            'Consultoria de Negócios',

            // Conteúdo e Comunicação
            'Redação',
            'Tradução',
            'Revisão de Texto',
            'Transcrição',
            'Criação de Conteúdo',
            'Edição e Revisão de Texto',
            'Storytelling',

            // Suporte e Administração
            'Assistente Virtual',
            'Suporte Técnico',
            'Suporte ao Cliente',
            'Administração e Escritório',
            'Financeiro e Contabilidade',
            'Consultoria Financeira',

            // Outras
            'Arquitetura',
            'Engenharia',
            'Educação e Tutoria',
            'Coaching e Desenvolvimento Pessoal',
        ];

        foreach ($categories as $categoryName) {
            Category::updateOrCreate(['name' => $categoryName]);
        }
    }
}
