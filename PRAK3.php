<?php
//MUHAMMAD ALTHOOFANDY SUPRAYOGI 1900018410
//TEKNIK OPTIMASI
class Parameters
{
    const FILE_NAME = 'products.txt';
    const COLUMNS = ['item', 'price'];
    const POPULATION_SIZE = 10;
    const BUDGET = 280000;
    const STOPING_VALUE = 10000;
    const CROSSOVER_RATE = 0.8;
}

class Catalogue //untuk membaca file txt produk
{

    function createProductColumn($listOfRawProduct){
        //membaca setiap kolom dari item produk dengan menggunakan key
        foreach (array_keys($listOfRawProduct) as $listOfRawProductKey){
            // mengganti index menjadi item dan price
            $listOfRawProduct[Parameters::COLUMNS[$listOfRawProductKey]] = $listOfRawProduct[$listOfRawProductKey];
            unset($listOfRawProduct[$listOfRawProductKey]);//mengosongkan kembali array yang sebelumnya
        }
        return $listOfRawProduct; //return value yang sudah berubah dari inex 0 1 menjadi item price
                                //dan akan disimpan di array collectionOfListProduct[]
    }
    // fungsi untuk memanggil file txt
    function product(){
        $collectionOfListProduct = [];

        $raw_data = file(Parameters::FILE_NAME);//memanggil nama file
        //membaca item tiap baris kemudian disimpan
        foreach ($raw_data as $listOfRawProduct){
             $collectionOfListProduct[] = $this->createProductColumn(explode(",", $listOfRawProduct));
        }
        // melihat katalog produk
        //foreach ($collectionOfListProduct as $listOfRawProduct){
        //   print_r($listOfRawProduct);
        //   echo '<br>';
        //}
        return $collectionOfListProduct;
    }
}
class Individu
{
    function countNumberofGen(){
        $catalogue = new Catalogue;
        return count($catalogue->product());
    }
    function createRandomIndividu(){
        //echo $this->countNumberofGen();exit();
        for($i = 0;$i <= $this->countNumberofGen()-1;$i++){
            $ret[] = rand(0,1);
        }
        return $ret;
    }
}
class Population //membuat populasi awal
{
    function createRandomPopulation(){
        $individu = new Individu;
        for ($i = 0; $i <= Parameters::POPULATION_SIZE-1; $i++){
           $ret[] = $individu -> createRandomIndividu();
        }
        return $ret;
        //foreach ($ret as $key => $val){
        //    print_r($val);
        //    echo '<br>';
        //}
    }
}

class Fitness
{
    function selectingItem($individu){//fungsi untuk menyeleksi item yang bernilai 1 berarti masuk ke parcel
        $catalogue =  new Catalogue;
        foreach($individu as $individuKey => $binaryGen){
            if($binaryGen === 1){
                $ret[] = [
                    'selectedKey' => $individuKey,
                    'selectedPrice' => $catalogue->product()[$individuKey]['price']
                ];
            }
        }
        return $ret;
    }
    function calculateFitnessValue($individu){ //untuk menghitung nilai fitness / nilai total belanja dari setiap indinvidu
        return array_sum(array_column($this->selectingItem($individu),'selectedPrice'));
    }
    function countSelectedItem($individu){
        return count($this->selectingItem($individu));// untuk emnghitung total item barang
    }
    function searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem){//untuk mencari individu yang terbaik dalam memilih item 
        if($numberOfIndividuHasMaxItem === 1){
            $index = array_search($maxItem, array_column($fits, 'numberOfSelectedItem'));
            
            return $fits[$index];
            
        } else {
            foreach($fits as $key => $val){
                if($val['numberOfSelectedItem'] === $maxItem){
                    echo $key.' '.$val['fitnessValue'].'<br>';
                    $ret[] = [
                        'individuKey' => $key,
                        'fitnessValue' => $val['fitnessValue']
                    ];
                }
            }
            if(count(array_unique(array_column($ret, 'fitnessValue'))) === 1){
                $index = rand(0, count($ret) - 1);
            } else {
                $max = max(array_column($ret, 'fitnessValue'));
                $index = array_search($max, array_column($ret, 'fitnessValue'));
            }
            echo 'Hasil';
            return $ret[$index];
        }
    }
    function isFound($fits){// fungsi untuk mengetahui solusi terbaik
        $countedMaxItems = array_count_values(array_column($fits, 'numberOfSelectedItem'));
        print_r($countedMaxItems);
        echo '<br>';
        $maxItem = max(array_keys($countedMaxItems));// menghitung item tertinggi dari masing masing individu
        echo $maxItem;
        echo '<br>';
        echo $countedMaxItems[$maxItem];
        $numberOfIndividuHasMaxItem = $countedMaxItems[$maxItem];
        
        $bestFitnessValue = $this->searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem)['fitnessValue'];
        echo '<br>';
        echo '<br> Best Fitness Value: '.$bestFitnessValue;

        $residual = Parameters::BUDGET -$bestFitnessValue;
        echo ' Residual: '.$residual;

        if($residual<=Parameters::STOPING_VALUE && $residual > 0){
            return TRUE;
        }
    }
    function isFit($fitnessValue){//menghitung apakah sesuai budget apa tidak
        if ($fitnessValue <= Parameters::BUDGET){
            return TRUE;
        }
    }
    function fitnessEvaluation($population){
        $catalogue = new Catalogue;
        foreach ($population as $listOfIndividuKey => $listOfIndividu){
            echo 'Individu-'.$listOfIndividuKey.'<br>';
            foreach ($listOfIndividu as $individuKey => $binaryGen){
                echo $binaryGen.'&nbsp;&nbsp;';
                print_r($catalogue->product()[$individuKey]);
                echo '<br>';//menampilkan data produk dan menyeleksi
            }
            $fitnessValue = $this->calculateFitnessValue($listOfIndividu);
            $numberOfSelectedItem = $this->countSelectedItem($listOfIndividu);
            echo 'Max. Item: '.$numberOfSelectedItem;
            echo ' Harga total: '.$fitnessValue;
            if ($this->isFit($fitnessValue)){
                echo '(Fit)';
                $fits[] = [
                    'selectedIndividuKey' => $listOfIndividuKey,
                    'numberOfSelectedItem' => $numberOfSelectedItem,
                    'fitnessValue' => $fitnessValue
                ];
                print_r($fits);
            } else {
                echo '(Not Fit)';
            }
            echo '<p>';
        }
        if($this->isFound($fits)){
            echo 'Found';
        }else {
            echo ' >>Next Generation';
        }
    }
}
class Crossover
{

    public $population;

    function __construct($population)
    {
        $this->population = $population;
    }

    function randomZeroToOne()
{

    return(float)rand()/(float)getrandmax();
}

    function generateCrossover()
    {

    for ($i=0; $i < Parameters::POPULATION_SIZE-1 ; $i++) { 
        $randomZeroToOne = $this -> randomZeroToOne();
        if ($randomZeroToOne<Parameters::CROSSOVER_RATE) {
            $parents[$i]=$randomZeroToOne;
            // code...
        }
    }
    

    foreach(array_keys($parents) as $key){

        foreach(array_keys($parents) as $subkey){
            if ($key !== $subkey) {
                $ret[]= [$key, $subkey];
                
            }
        }
        array_shift($parents);
    }
    return $ret;
        
    }
    function offspring($parent1,$parent2,$cutPointIndex,$offspring){
        $lengthofGen = new Individu;
        if ($offspring==1) {
            for ($i=0; $i <$lengthofGen->countNumberofGen()-1 ; $i++) {
            if($i <=$cutPointIndex)
            $ret[]=   $parent1[$i]; 
                // code...
            }
            if ($i>$cutPointIndex) {
                $ret[]=$parent2[$i];
                // code...
            }
            // code...
        }
        if ($offspring==2) {
            for ($i=0; $i <$lengthofGen->countNumberofGen()-1 ; $i++) {
            if($i <=$cutPointIndex)
            $ret[]=   $parent1[$i]; 
                // code...
            }
            if ($i>$cutPointIndex) {
                $ret[]=$parent2[$i];
                // code...
            }
            // code...
        }
        return $ret;
    
    }
    function cutPointRandom(){
        $lengthofGen= new Individu;
        return rand(0, $lengthofGen->countNumberofGen()-1);
    }
    function crossover()
    {

        $cutPointIndex=$this->cutPointRandom();
        echo $cutPointIndex;
        foreach ($this->generateCrossover() as $listofCrossover){
            $parent1 = $this->population[$listofCrossover[0]];
            $parent2 = $this->population[$listofCrossover[1]];
            echo 'Parents :<br>';
            foreach($parent1 as $gen){
                echo $gen;
            }
            echo ' >< ';
            foreach($parent2 as $gen){
                echo $gen;
            }
                echo '<br>';
                echo 'offspring <br>';
                $offspring1=$this->offspring($parent1,$parent2,$cutPointIndex,1);
                 $offspring2=$this->offspring($parent1,$parent2,$cutPointIndex,2);
                
                foreach($offspring1 as $gen){
                echo $gen;
            }
            echo ' >< ';
            foreach($offspring2 as $gen){
                echo $gen;
            }
        }       

    }
}

//array untuk menyimpan parameter yang digunakan
$parameters = [
    'file_name' => 'products.txt',
    'columns' => ['item', 'price'],
    'population_size' => 10
];

//$katalog = new Catalogue;
//$katalog -> product($parameters);

$initialPopulation = new Population;
$population = $initialPopulation->createRandomPopulation();

$fitness = new Fitness;
$fitness->fitnessEvaluation($population);

$crossover = new Crossover($population);
$crossover->crossover();

//$individu = new Individu;
//print_r($individu->createRandomIndividu());