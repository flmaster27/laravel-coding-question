Тестовое задание
Дано
Есть 4 загона в которых вырастает живность как показана на рисунке:
  
Тестовое задание:
•	Написать API с использованием RESTful
•	Работу на FronEnd-e с написанным API
•	Проектирование бд

Правила:
•	4 загона для овечек
•	изначальное количество овечек 10 расположены рандомно по загонам 
•	1 день длится 10 секунд
•	каждый день в одном из загонов где больше 1 овечки появляется ещё одна овечка
•	каждый 10 день одну любую овечку забирают(сами знаете куда)
•	если в загоне осталась одна овечка то берём загон где больше всего овечек и пересаживаем одну из них к одинокой овечке
•	загоны никогда не должны быть пусты
•	должен вестись счёт дней, который не обнуляется при обновлении страницы
•	должна быть история действий происходящих в загонах(выводить никуда не надо)

Плюсом будем:
•	Страничка с отчётом за каждый день или период дней по запросу. Где мы должны видеть:
o	общее количество овечек 
o	количество убитых овечек 
o	количество живых овечек
o	номер самого населённого загона
o	номер самого менее населённого загона

Примечание:
Для решения данной задачи предлагается использовать следующие технологии\языки\фреймворки\библиотеки:
•	BackEnd:
o	Laravel 5.3
o	MySQL или PostgreSQL или SQLite
•	FrontEnd:
o	Vue JS, Bootstrap или любой другой
