<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disciplinas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Cada disciplina tem 3 topicos. O topico e a unidade de modelagem do
        // conhecimento: a proficiencia e estimada por topico, nao por disciplina.
        Schema::create('topicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disciplina_id')->constrained('disciplinas')->cascadeOnDelete();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // A dificuldade usa a mesma escala da proficiencia do estudante, o que
        // permite calcular a probabilidade esperada de acerto.
        Schema::create('questoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topico_id')->constrained('topicos')->cascadeOnDelete();
            $table->text('enunciado');
            $table->string('nivel');
            $table->unsignedSmallInteger('dificuldade');
            $table->timestamps();
        });

        Schema::create('alternativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questao_id')->constrained('questoes')->cascadeOnDelete();
            $table->char('letra', 1);
            $table->text('texto');
            $table->boolean('correta')->default(false);
            $table->timestamps();
        });

        // Preferencia de tamanho de prova do estudante, guardada na conta para
        // que ele nao precise reinformar a cada retorno.
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('tamanho_prova')->default(20)->after('password');
        });

        /**
         * Estado do conhecimento: uma linha por topico por estudante.
         *
         * Fica vinculado a conta, e nao a uma sessao de navegacao, para que o
         * modelo do estudante persista entre visitas. Assim o algoritmo retoma
         * de onde parou em vez de recomecar do zero a cada retorno.
         *
         * "ultima_pratica" guarda o instante em que o topico apareceu pela
         * ultima vez em uma prova corrigida. E a partir dele que se calcula o
         * intervalo em dias usado pelo fator de espacamento.
         */
        Schema::create('proficiencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('topico_id')->constrained('topicos')->cascadeOnDelete();
            $table->float('pontuacao')->default(1200);
            $table->timestamp('ultima_pratica')->nullable();
            $table->unsignedInteger('acertos')->default(0);
            $table->unsignedInteger('erros')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'topico_id']);
        });

        /**
         * Prova: conjunto fechado de questoes montado de uma vez.
         *
         * A primeira prova do estudante e diagnostica, com as questoes
         * distribuidas uniformemente entre os topicos, porque ainda nao ha
         * informacao sobre ele. As seguintes sao otimizadas: a distribuicao
         * segue os pesos calculados pelo recomendador a partir do desempenho
         * acumulado em todas as provas anteriores.
         */
        Schema::create('provas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('numero');          // 1, 2, 3...
            $table->string('tipo');                          // diagnostica | otimizada
            $table->unsignedSmallInteger('tamanho');
            $table->string('status')->default('em_andamento'); // em_andamento | concluida
            $table->unsignedSmallInteger('acertos')->nullable();
            $table->timestamp('concluida_em')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'numero']);
        });

        Schema::create('prova_questoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prova_id')->constrained('provas')->cascadeOnDelete();
            $table->foreignId('questao_id')->constrained('questoes')->cascadeOnDelete();
            $table->unsignedSmallInteger('ordem');
            $table->foreignId('alternativa_id')->nullable()->constrained('alternativas')->nullOnDelete();
            $table->boolean('correta')->nullable();
            $table->timestamps();
            $table->unique(['prova_id', 'questao_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prova_questoes');
        Schema::dropIfExists('provas');
        Schema::dropIfExists('proficiencias');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tamanho_prova');
        });
        Schema::dropIfExists('alternativas');
        Schema::dropIfExists('questoes');
        Schema::dropIfExists('topicos');
        Schema::dropIfExists('disciplinas');
    }
};
