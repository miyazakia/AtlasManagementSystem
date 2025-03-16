<?php
namespace App\Calendars\General;

use Carbon\Carbon;
use Auth;

class CalendarView{

  private $carbon;
  function __construct($date){
    $this->carbon = new Carbon($date);
  }

  public function getTitle(){
    return $this->carbon->format('Y年n月');
  }

  function render(){
    $html = [];
    $html[] = '<div class="calendar text-center">';
    $html[] = '<table class="table">';
    $html[] = '<thead>';
    $html[] = '<tr>';
    $html[] = '<th>月</th>';
    $html[] = '<th>火</th>';
    $html[] = '<th>水</th>';
    $html[] = '<th>木</th>';
    $html[] = '<th>金</th>';
    $html[] = '<th>土</th>';
    $html[] = '<th>日</th>';
    $html[] = '</tr>';
    $html[] = '</thead>';
    $html[] = '<tbody>';
    $weeks = $this->getWeeks();
    foreach($weeks as $week){//週ごとの表示
      $html[] = '<tr class="'.$week->getClassName().'">';

      $days = $week->getDays();
      foreach($days as $day){//過去か未来化の判定
        $startDay = $this->carbon->copy()->format("Y-m-01");
        $toDay = $this->carbon->copy()->format("Y-m-d");

        if($startDay <= $day->everyDay() && $toDay >= $day->everyDay()){
          $html[] = '<td class="past-day calendar-td">';
        }else{
          $html[] = '<td class="calendar-td '.$day->getClassName().'">';
        }
        $html[] = $day->render();

        //予約の有無の判定
        if(in_array($day->everyDay(), $day->authReserveDay())){
          $reservePart = $day->authReserveDate($day->everyDay())->first()->setting_part;
          //リモ1部、リモ2部、リモ3部の分岐
          if($reservePart == 1){
            $reservePart_text = "リモ1部";
            $reservePart_past ="1部参加";
          }else if($reservePart == 2){
            $reservePart_text = "リモ2部";
            $reservePart_past ="2部参加";
          }else if($reservePart == 3){
            $reservePart_text = "リモ3部";
            $reservePart_past ="3部参加";
          }
          //予約していた場合
          //未来か過去か↓
          if($startDay <= $day->everyDay() && $toDay >= $day->everyDay()){
            //予約していたら(過去情報)
            $html[] = '<p class="m-auto p-0 w-75" style="font-size:12px">'. $reservePart_past .'</p>';
            $html[] = '<input type="hidden" name="getPart[]" value="" form="reserveParts">';
          }else{
            // 予約していたら(未来情報)モーダルでキャンセル
            $html[] = '<button type="button" class="btn btn-danger p-0 w-75 js-modal-open" name="delete_date" reserve_part="'.$reservePart.'" reserve_date="'.$day->authReserveDate($day->everyDay())->first()->setting_reserve.'" style="font-size:12px">'. $reservePart_text .'</button>';
            $html[] = '<input type="hidden" name="getPart[]" value="" form="reserveParts">';
          }
        }else{
          //予約をしていなかった場合
          //未来か過去か↓
          if($startDay <= $day->everyDay() && $toDay >= $day->everyDay()){
            //予約していない（過去情報）
            $html[] = '<p class="m-auto p-0 w-75" style="font-size:12px">受付終了</p>';
            $html[] = '<input type="hidden" name="getPart[]" value="" form="reserveParts">';
          }else{
            //予約していない（未来情報）
          $html[] = $day->selectPart($day->everyDay());
        }
        }
        $html[] = $day->getDate();
        $html[] = '</td>';
      }
      $html[] = '</tr>';
    }
    $html[] = '</tbody>';
    $html[] = '</table>';
    $html[] = '</div>';
    $html[] = '<form action="/reserve/calendar" method="post" id="reserveParts">'.csrf_field().'</form>';
    $html[] = '<form action="/delete/calendar" method="post" id="deleteParts">'.csrf_field().'</form>';

    return implode('', $html);
  }


  protected function getWeeks(){
    $weeks = [];
    $firstDay = $this->carbon->copy()->firstOfMonth();
    $lastDay = $this->carbon->copy()->lastOfMonth();
    $week = new CalendarWeek($firstDay->copy());
    $weeks[] = $week;
    $tmpDay = $firstDay->copy()->addDay(7)->startOfWeek();
    while($tmpDay->lte($lastDay)){
      $week = new CalendarWeek($tmpDay, count($weeks));
      $weeks[] = $week;
      $tmpDay->addDay(7);
    }
    return $weeks;
  }
}
