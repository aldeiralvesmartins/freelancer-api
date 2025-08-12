<?php

namespace App\Services\Asaas;

use App\Enums\FinancialMovement as FinancialMovementEnum;
use App\Enums\OccurrenceMovementEnum;
use App\Models\OccurrenceMovement;
use App\Models\Dojo;
use App\Models\FinancialMovement;
use App\Models\Student;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

/**
 * Serviço de integração com a API do Asaas para geração de boletos.
 */
class AsaasService
{
    protected array $token;
    private array $url;
    private object $client;

    public function __construct()
    {
        $enverimenteUrl = getenv('ASAAS_DOMAIN');
        $this->url = [
            'criarConta' => "$enverimenteUrl/v3/accounts",
            'criarCobranca' => "$enverimenteUrl/v3/payments",
            'criarCliente' => "$enverimenteUrl/v3/customers",
            'verificarDocumentoAsaas' => "$enverimenteUrl/v3/myAccount/documents"
        ];
        $this->client = new Client();
    }

    /**
     * Criar Cobranca.
     * @return string|true
     * @throws GuzzleException
     * @throws Exception
     */
    public function criarCobranca($dados): bool|string
    {
        try {
            $response = $this->client->post($this->url['criarCobranca'],
                ['headers' => $this->prepareHeadersCliente($dados['api_key']), 'json' => $this->prepararDadosParaGerarCobranca($dados)]);
            $retorno = json_decode($response->getBody()->getContents(), true);
            $retorno['financial_movement_id'] = $dados['id'];
            return $this->trataRetornoCobranca($retorno);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * Remove Cobranca.
     * @return array|true
     * @throws GuzzleException
     * @throws Exception
     */
    public function removerCobranca($dados): bool|array
    {
        try {
            $response = $this->client->delete("{$this->url['criarCobranca']}/{$dados['payment_id']}",
                ['headers' => $this->prepareHeadersCliente($dados['api_key'])]);
            $retorno = json_decode($response->getBody()->getContents(), true);
            if (!array_key_exists('deleted', $retorno)) {
                throw new \Exception('Campo "deleted" não encontrado no retorno da API do Asaas.');
            }
            if (!$retorno['deleted']) {
                throw new \Exception('A cobrança não pôde ser cancelada pelo Asaas.');
            }
            unset( $dados['api_key']);
            return $this->trataRetornoRemoveCobranca($dados);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * Cria Cliente.
     *
     * @return string|true
     * @throws GuzzleException
     * @throws Exception
     */
    public function criarCliente($dados): bool|string
    {
        try {
            $response = $this->client->post($this->url['criarCliente'],
                ['headers' => $this->prepareHeadersCliente($dados['api_key']), 'json' => $this->prepararDadosParaGerarCliente($dados)]);
            $retorno = json_decode($response->getBody()->getContents(), true);
            if (!empty($retorno['id']))
                $this->CriaVinculoCliente($dados, $retorno['id']);
            return $retorno['id'];
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * Cria SubConta.
     *
     * @return array|true
     * @throws GuzzleException
     * @throws Exception
     */
    public function CriarSubConta(): bool|array
    {
        try {
            $response = $this->client->post($this->url['criarConta'],
                ['headers' => $this->prepareHeaders(), 'json' => $this->prepararDadosParaGerarSubConta()]);
            $retorno = json_decode($response->getBody()->getContents(), true);
            return $this->trataRetorno($retorno);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $body = (string)$response->getBody();
                $errorData = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($errorData['errors'][0]['description'])) {
                    throw new Exception($errorData['errors'][0]['description'],);
                } else {
                    throw new Exception('Ocorreu um erro ao processar sua solicitação. Tente novamente.');
                }
            } else {
                throw new Exception('Erro ao se comunicar com o servidor.');
            }
        }
    }

    /**
     * Verificar Documento Asaas.
     *
     * @return array|true
     * @throws GuzzleException
     * @throws Exception
     */
    public function verificarDocumentoAsaas($contaApiKey): bool|array
    {
        try {
            $response = $this->client->get($this->url['verificarDocumentoAsaas'],
                ['headers' => $this->prepareHeadersCliente($contaApiKey)]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param $dados
     * @return array
     * @throws Exception
     */
    private function prepararDadosParaGerarSubConta(): array
    {
        $dojo = Dojo::with('address')->find(auth()->user()->dojo_id);
        $phoneNumber = optional($dojo->phones->first())->number ?? '';
        $cpfCnpj = $this->removerCaracteresNaoNumericos($dojo['taxpayer']);
        $cep = $this->removerCaracteresNaoNumericos($dojo->address->zip_code);
        $numeroTelefone = $this->removerCaracteresNaoNumericos($phoneNumber);
        $bairro = $this->removerAcentos($dojo->address->neighborhood);
        $addressNumber = $this->removerAcentos($dojo->address->number);
        $complement = $this->removerAcentos($dojo->address->complement ?? '');
        $address = $this->removerAcentos($dojo->address->street);
        $valor = 15000;
        $companyType = 'LIMITED';
        $url = getenv('URL_WEBHOOK');
        return [
            'name' => $dojo['name'],
            'email' => $dojo['email'],
            'cpfCnpj' => $cpfCnpj,
            'birthDate' => date('Y-m-d', strtotime($dojo->foundation_date)) ?? null,
            'companyType' => $companyType,
            'phone' => $numeroTelefone,
            'mobilePhone' => $numeroTelefone,
            'address' => "$address, $bairro",
            'addressNumber' => $addressNumber,
            'complement' => $complement,
            'province' => $bairro,
            'postalCode' => $cep,
            'incomeValue' => $valor,
            'webhooks' => [
                [
                    'name' => "Webhook para {$dojo['name']}",
                    'url' => $url,
                    'email' => 'suporte@cenfit.com.br',
                    'sendType' => 'SEQUENTIALLY',
                    'interrupted' => false,
                    'enabled' => true,
                    'apiVersion' => 3,
                    'authToken' => '5tLxsL6uoN',
                    'events' => ['PAYMENT_CREATED', 'PAYMENT_UPDATED', 'PAYMENT_OVERDUE', 'PAYMENT_RECEIVED','PAYMENT_DELETED']
                ]
            ]
        ];
    }

    /**
     * @param $dados
     * @return array
     * @throws Exception
     */
    private function prepararDadosParaGerarCobranca($dados): array
    {
        $customerId = $this->getCustomerId($dados['saleable_id']);
        if (!$customerId) {
            $customerId = $this->criarCliente($dados);
        }
        $mensagem = "Parcela {$dados['number']} Valor R$ " . number_format($dados['amount'], 2, ',', '.') . " ";
        $mensagem .= "Entrada " . ($dados['entrance'] ? 'Sim' : 'Não') . " ";
        $mensagem .= "Vencimento " . date('d/m/Y', strtotime($dados['expiration_date']));

        return array_merge([
            'customer' => $customerId,
            'billingType' => 'BOLETO',
            'dueDate' => $dados['expiration_date'],
            'value' => $dados['amount'],
            'description' => $mensagem ?? null,
            'externalReference' => $dados['id'],
            'postalService' => false
        ], $this->regrasDeDescontoMultaJurosSplitParaPagamento($dados));
    }

    /**
     * @param $dados
     * @return array
     * @throws Exception
     */
    private function prepararDadosParaGerarCliente($dados): array
    {
        $cliente = Student::with('address')->find($dados['saleable_id']);
        $documentoPagador = $this->removerCaracteresNaoNumericos($cliente->taxpayer);
        $cep = $this->removerCaracteresNaoNumericos($cliente->address->zip_code);
        $bairro = $this->removerAcentos($cliente->address->neighborhood);
        $addressNumber = $this->removerAcentos($cliente->address->number);
        $complement = $this->removerAcentos($cliente->address->complement);
        $address = $this->removerAcentos($cliente->address->street);
        return [
            'name' => $cliente->name,
            'email' => 'jarlindopereira@gmail.com',
            'cpfCnpj' => $documentoPagador,
            'postalCode' => $cep,
            'address' => $address,
            'addressNumber' => $addressNumber,
            'complement' => $complement,
            'province' => $bairro,
            'externalReference' => $documentoPagador,
            'notificationDisabled' => true
        ];
    }

    /**
     * @param $dados
     * @return array
     * @throws Exception
     */
    private function trataRetorno($dados): array
    {
        return [
            'name' => 'AG:' . $dados['accountNumber']['agency'] . ' C:' . $dados['accountNumber']['account'] . '-' . $dados['accountNumber']['accountDigit'],
            'account_number' => $dados['accountNumber']['account'],
            'account_digit' => $dados['accountNumber']['accountDigit'],
            'agency' => $dados['accountNumber']['agency'],
            'wallet_id' => $dados['walletId'],
            'api_key' => $dados['apiKey'],
            'status' => true,
            'dojo_id' => auth()->user()->dojo_id,
        ];
    }

    /**
     * Trata o retorno da cobrança bancária.
     * @param array $dados
     * @return bool|string URL do boleto em caso de sucesso, false em caso de erro.
     * @throws Exception
     */
    private function trataRetornoCobranca(array $dados): bool|string
    {
        try {
            $movement = FinancialMovement::find($dados['financial_movement_id']);
            if (!$movement) {
                Log::warning("Movimentação financeira com ID {$dados['financial_movement_id']} não encontrada.");
            } else {
                $movement->boleto_link = $dados['bankSlipUrl'];
                $movement->status = FinancialMovementEnum::PendingEntry;
                $movement->save();
            }
            $ocorrencia = OccurrenceMovement::create([
                'situation' => OccurrenceMovementEnum::SITUATION_PENDING_ENTRY,
                'document_number' => $dados['invoiceNumber'],
                'our_number' => $dados['nossoNumero'],
                'financial_movement_id' => $dados['financial_movement_id'],
                'type' => OccurrenceMovementEnum::TYPE_REMESSA,
                'digitable_line' => $dados['identificationField'] ?? null,
                'payment_id' => $dados['id'],
                'boleto_info_link' => $dados['invoiceUrl'],
                'boleto_link' => $dados['bankSlipUrl'],
                'customer_id' => $dados['customer'],
                'dojo_id' => auth()->user()->dojo_id,
            ]);
            if (!empty($ocorrencia))
                return $dados['bankSlipUrl'];
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * Trata o retorno da cobrança baixad.
     * @param array $dados
     * @return bool|string URL do boleto em caso de sucesso, false em caso de erro.
     * @throws Exception
     */
    private function trataRetornoRemoveCobranca($dados): bool|string
    {
        try {
            $movement = FinancialMovement::find($dados['financial_movement_id']);
            if (!$movement) {
                Log::warning("Movimentação financeira com ID {$dados['financial_movement_id']} não encontrada.");
            } else {
                $movement->boleto_link = $dados['bankSlipUrl'];
                $movement->status = FinancialMovementEnum::PendingLow;
                $movement->save();
            }
            $ocorrencia = OccurrenceMovement::create([
                'situation' => OccurrenceMovementEnum::SITUATION_PENDING_LOW,
                'document_number' => $dados['document_number'],
                'our_number' => $dados['our_number'],
                'financial_movement_id' => $dados['financial_movement_id'],
                'type' => OccurrenceMovementEnum::TYPE_REMESSA,
                'digitable_line' => $dados['digitable_line'] ?? null,
                'payment_id' => $dados['payment_id'],
                'boleto_info_link' => $dados['boleto_info_link'],
                'boleto_link' => $dados['boleto_link'],
                'customer_id' => $dados['customer_id'],
                'dojo_id' => auth()->user()->dojo_id,
            ]);
            if (!empty($ocorrencia))
                return true;
        } catch (\Exception $e) {
            debug($e->getMessage());
            Log::error("Erro ao tratar retorno de cobrança: " . $e->getMessage());
            return false;
        }
        return false;
    }

    /**
     * @param $dados
     * @param $customerId
     * @throws Exception
     */
    private function CriaVinculoCliente($dados, $customerId): void
    {
        $responsavel = Student::find($dados['saleable_id']);
        if ($responsavel) {
            $responsavel->customer_id = $customerId;
            $responsavel->save();
        } else {
            Log::warning("Aluno com ID {$dados['saleable_id']} não encontrado.");
        }
    }

    /**
     * @param $saleableId
     * @return bool|string
     */
    private function getCustomerId($saleableId): bool|string
    {
        $cliente = Student::find($saleableId);
        return $cliente['customer_id'] ?? false;
    }

    /**
     * @param $dados
     * @return array
     * @throws \DateMalformedStringException
     */
    private function regrasDeDescontoMultaJurosSplitParaPagamento($dados): array
    {
        $dadosCobranca = [];
//        $movimentacaoRenegociacao = json_decode($dados['rateio'][0]['movimentacao_renegociacao'], true) ?? [];
//        foreach ($movimentacaoRenegociacao as $index => $rateio) {
//            if (!empty($rateio['chave']))
//                $dadosCobranca['split'][$index] = [
//                    "externalReference" => (string)$rateio['id'],
//                    "walletId" => $rateio['chave'],
//                    "fixedValue" => $rateio['valor'],
//                ];
//        }
        if (!empty($dados['desconto']) && $dados['desconto'] > 0) {
            $dataVencimento = new DateTime($dados['dataVencimento']);
            $dataDesconto = new DateTime($dados['data_desconto']);
            $diasAntesVencimento = $dataVencimento->diff($dataDesconto)->days;
            $dadosCobranca['discount'] = [
                'value' => $dados['desconto'],
                'dueDateLimitDays' => $diasAntesVencimento
            ];
        }
        if (!empty($dados['multa_em_percentual']) && $dados['multa_em_percentual'] > '0.00') {
            $dadosCobranca['fine'] = [
                'value' => $dados['multa_em_percentual']
            ];
        }
        if (!empty($dados['mora_dia_em_percentual']) && $dados['mora_dia_em_percentual'] > '0.00') {
            $dadosCobranca['interest'] = [
                'value' => $dados['mora_dia_em_percentual']
            ];
        }
        return $dadosCobranca;
    }

    /**
     * Monta os headers necessários para requisição.
     * @return array
     */
    private function prepareHeaders(): array
    {
        return [
            'access_token' => getenv('ASAAS_KEY'),
            'Content-Type' => 'application/json'
        ];
    }

    /**
     * Monta os headers necessários para requisição.
     */
    private function prepareHeadersCliente($apiKey): array
    {
        return [
            'access_token' => $apiKey,
            'Content-Type' => 'application/json'
        ];
    }

    /**
     * Remove caracteres não numéricos de um CEP.
     * @param string $string
     * @return string
     */
    public function removerCaracteresNaoNumericos(string $string): string
    {
        return preg_replace('/\D/', '', $string);
    }

    /**
     * Remove os acentos de um texto.
     * @param ?string $texto
     * @return string
     */
    public function removerAcentos(?string $texto): string
    {
        if (is_null($texto)) {
            return '';
        }
        return strtr($texto, [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A', 'Ä' => 'A',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'Ó' => 'O', 'Ò' => 'O', 'Õ' => 'O', 'Ô' => 'O', 'Ö' => 'O',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'ç' => 'c', 'Ç' => 'C'
        ]);
    }
}
