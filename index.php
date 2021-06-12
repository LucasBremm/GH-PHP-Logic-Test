<?php
    $data = file_get_contents('funcionarios.json');
    $parsed_data = json_decode($data, true);

    $funcionarios = $parsed_data['funcionarios'];
    $areas = transform_areas($parsed_data['areas']);

    // calculo do salario maximo, minimo e médio geral
    calculate_geral($funcionarios);
    echo '<br><br>';

    // calculo do salário maximo, minimo e médio por area 
    calculate_areas($funcionarios, $areas);
    echo '<br>';

    // calculo de qual a area com mais e com menos funcionarios
    get_most_and_least_funcs($funcionarios, $areas);

    function transform_areas($areas) {
        $transformed_areas = [];

        foreach ($areas as $area) {
            $transformed_areas[$area['codigo']] = $area['nome'];
        }

        return $transformed_areas;
    } 

    function compare_min($func_min, $func_cmp) {
        if (!$func_min) {
            return $func_cmp;
        }

        if ($func_cmp['salario'] < $func_min['salario']) {
            return $func_cmp;
        }

        return $func_min;
    }

    function compare_max($func_max, $func_cmp) {
        if (!$func_max) {
            return $func_cmp;
        }

        if ($func_cmp['salario'] > $func_max['salario']) {
            return $func_cmp;
        }

        return $func_max;
    }

    function print_func($funcionario, $start) {
        $return_format = $start . ' | %s %s | %.2f';
        printf(
            $return_format, 
            $funcionario['nome'], 
            $funcionario['sobrenome'], 
            $funcionario['salario']
        );
    }

    function print_func_with_area($funcionario, $area, $start) {
        $return_format = $start . ' | %s | %s %s | %.2f';
        printf(
            $return_format, 
            $area,
            $funcionario['nome'], 
            $funcionario['sobrenome'], 
            $funcionario['salario']
        );
    }

    function get_all_funcs($funcionarios, $func_selected) {
        $salario = $func_selected['salario'];
        $all_funcs = [];
        foreach($funcionarios as $funcionario) {
            if ($funcionario['salario'] == $salario) {
                $all_funcs[] = $funcionario;
            }
        }

        return $all_funcs;
    }

    function calculate_values($funcionarios) {
        $min_geral = null;
        $max_geral = null;
        $avg = 0;

        foreach($funcionarios as $funcionario) {
            $min_geral = compare_min($min_geral, $funcionario);
            $max_geral = compare_max($max_geral, $funcionario);
            $avg += $funcionario['salario'];
        }

        $avg = $avg / count($funcionarios);
        
        $all_min = get_all_funcs($funcionarios, $min_geral);
        $all_max = get_all_funcs($funcionarios, $max_geral);
        return compact(['all_min', 'all_max', 'avg']);
    }


    function calculate_geral ($funcionarios) {
        $values = calculate_values($funcionarios);
        foreach ($values['all_max'] as $func) {
            print_func($func, 'max');
            echo '<br>';
        }
        foreach ($values['all_min'] as $func) {
            print_func($func, 'min');
            echo '<br>';
        }
        printf('avg | %.2f', $values['avg']);
    }

    function calculate_specific ($values, $areas) {
        foreach ($values['all_max'] as $func) {
            print_func_with_area($func, $areas[$func['area']], 'area_max');
            echo '<br>';
        }
        foreach ($values['all_min'] as $func) {
            print_func_with_area($func, $areas[$func['area']], 'area_min');
            echo '<br>';
        }
        printf('area_avg | %.2f', $values['avg']);
    }

    function get_funcs_by_area ($funcionarios, $areas) {
        $funcs_by_area = [];
        foreach($funcionarios as $funcionario) {
            $funcs_by_area[$funcionario['area']][] = $funcionario;
        }

        return $funcs_by_area;
    }

    function calculate_areas ($funcionarios, $areas) {
        $funcs_by_area = get_funcs_by_area($funcionarios, $areas);

        $values = [];
        foreach($areas as $codigo=>$nome) {
            $values = calculate_values($funcs_by_area[$codigo]);
            calculate_specific($values, $areas);
            echo '<br>';
        }
    }

    function get_most_and_least_funcs($funcionarios, $areas) {
        $funcs_by_area = get_funcs_by_area($funcionarios, $areas);
        $counts = [];
        foreach($areas as $codigo=>$nome) {
            $counts[$codigo] = count($funcs_by_area[$codigo]);
        }

        $most = null;
        $least = null;

        foreach($counts as $key=>$count) {
            if(!$most || $count > $counts[$most]) {
                $most = $key;
            }

            if (!$least || $count < $counts[$least]) {
                $least = $key;
            }
        }
        
        $format = '%s | %s | %d';
        printf($format, 'com_mais', $areas[$most], $counts[$most]);
        echo '<br>';
        printf($format, 'com_menos', $areas[$least], $counts[$least]);
    }