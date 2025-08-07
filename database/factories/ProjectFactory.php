<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $titulos = [
            'Sistema de Gestão de Vendas',
            'Aplicativo de Controle Financeiro Pessoal',
            'Plataforma de Ensino a Distância',
            'Portal de Notícias Regionais',
            'Sistema de Agendamento Online para Clínicas',
            'Dashboard de Monitoramento Logístico',
            'Site Institucional para Escritório de Contabilidade',
            'Sistema de RH com Controle de Ponto',
        ];

        $status = ['open', 'in_progress', 'completed'];


        return [
            'title' => $this->faker->randomElement($titulos),
            'description' => $this->generateProjectDescription(),
            'budget' => $this->faker->numberBetween(3000, 50000),
            'deadline' => $this->faker->dateTimeBetween('+10 days', '+4 months'),
            'status' => $this->faker->randomElement($status),
        ];
    }

    private function generateProjectDescription(): string
    {
        $descricoes = [
            <<<DESC
Objetivo: Criar um sistema web para controle de vendas, com cadastro de clientes, produtos e geração de relatórios.

Escopo: Desenvolvimento completo com painel administrativo, controle de permissões e relatórios mensais.

Tecnologias: Laravel, Livewire, MySQL.

Resultados esperados: Melhor organização comercial e aumento de produtividade da equipe de vendas.
DESC,

            <<<DESC
Objetivo: Desenvolver um app mobile que permita ao usuário acompanhar gastos mensais e metas de economia.

Escopo: Aplicativo com autenticação, gráficos dinâmicos e integração com contas bancárias via API.

Tecnologias: Flutter, Firebase, API Open Finance.

Resultados esperados: Usuários mais conscientes financeiramente e controle efetivo de finanças pessoais.
DESC,

            <<<DESC
Objetivo: Construir uma plataforma para cursos online com área do aluno, fórum e emissão de certificados.

Escopo: Sistema completo com upload de vídeo, questionários interativos e painel para instrutores.

Tecnologias: Laravel, Vue.js, PostgreSQL, AWS S3.

Resultados esperados: Ampliar o alcance da instituição de ensino e melhorar a experiência de aprendizado.
DESC,

            <<<DESC
Objetivo: Criar um portal para publicação de notícias locais, com sistema de categorias, busca e comentários.

Escopo: Área de administração para jornalistas, integração com redes sociais e painel de métricas de leitura.

Tecnologias: Laravel, Tailwind CSS, Redis.

Resultados esperados: Engajamento da comunidade local e maior visibilidade para conteúdos regionais.
DESC,

            <<<DESC
Objetivo: Implantar sistema de agendamento online com confirmação por e-mail e lembretes via WhatsApp.

Escopo: Cadastro de serviços, horários disponíveis, cancelamentos e painel do administrador.

Tecnologias: Laravel, Vue.js, Twilio API.

Resultados esperados: Redução de faltas em consultas e organização eficiente da agenda.
DESC,
        ];

        return $this->faker->randomElement($descricoes);
    }
}
