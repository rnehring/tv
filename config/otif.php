<?php

return [

    /*
    |----------------------------------------------------------------------
    | Data source
    |----------------------------------------------------------------------
    | 'epicor' – pull live from the Epicor SQL Server. 'sample' – keep the
    | seeded sample snapshot and never touch the ERP.
    */
    'source' => env('OTIF_SOURCE', 'sample'),

    'max_age_minutes' => env('OTIF_MAX_AGE_MINUTES', 60),
    'refresh_minutes' => env('OTIF_REFRESH_MINUTES', 10),

    // On-time target line drawn on the slide (percent).
    'target_percent' => env('OTIF_TARGET', 95),

    /*
    |----------------------------------------------------------------------
    | Business units (Epicor OrderHed/ShipHead.Company code => display name)
    |----------------------------------------------------------------------
    */
    'companies' => [
        ['code' => '10',  'name' => 'PureFlex'],
        ['code' => '20',  'name' => 'Nil-Cor'],
        ['code' => '30',  'name' => 'Ethylene'],
        ['code' => '40',  'name' => 'Hills-McCanna'],
        ['code' => '50',  'name' => 'RamParts'],
        ['code' => 'PV0', 'name' => 'PolyValve'],
        ['code' => 'CC0', 'name' => 'Conley'],
        ['code' => 'GS0', 'name' => 'Endurance'],
        ['code' => 'A65', 'name' => 'SAS'],
    ],

    /*
    |----------------------------------------------------------------------
    | Shipment queries (the OTIF headline)
    |----------------------------------------------------------------------
    | Both count DISTINCT shipped orders for :month / :year, grouped by company.
    | total    = every order shipped that month.
    | on_time  = orders shipped on/before the required date (ShipDate-ReqDate<1).
    | On-Time % = on_time / total. Ported from the legacy otif-graph.php.
    */
    'ship_query_total' => "
        SELECT ShipHead.Company AS company_code, COUNT(DISTINCT OrderHed.OrderNum) AS cnt
        FROM Erp.ShipHead AS ShipHead
        LEFT OUTER JOIN Erp.ShipDtl AS ShipDtl ON ShipHead.Company = ShipDtl.Company AND ShipHead.PackNum = ShipDtl.PackNum
        INNER JOIN Erp.OrderRel AS OrderRel ON ShipDtl.Company = OrderRel.Company AND ShipDtl.OrderNum = OrderRel.OrderNum AND ShipDtl.OrderLine = OrderRel.OrderLine AND ShipDtl.OrderRelNum = OrderRel.OrderRelNum
        INNER JOIN Erp.OrderHed AS OrderHed ON ShipDtl.Company = OrderHed.Company AND ShipDtl.OrderNum = OrderHed.OrderNum
        WHERE ShipHead.ReadyToInvoice = 1 AND OrderHed.OpenOrder = 0
          AND DATEPART(month, ShipHead.ShipDate) = :month AND DATEPART(year, ShipHead.ShipDate) = :year
          AND OrderRel.ReqDate IS NOT NULL
        GROUP BY ShipHead.Company
    ",

    'ship_query_on_time' => "
        SELECT ShipHead.Company AS company_code, COUNT(DISTINCT OrderHed.OrderNum) AS cnt
        FROM Erp.ShipHead AS ShipHead
        LEFT OUTER JOIN Erp.ShipDtl AS ShipDtl ON ShipHead.Company = ShipDtl.Company AND ShipHead.PackNum = ShipDtl.PackNum
        INNER JOIN Erp.OrderRel AS OrderRel ON ShipDtl.Company = OrderRel.Company AND ShipDtl.OrderNum = OrderRel.OrderNum AND ShipDtl.OrderLine = OrderRel.OrderLine AND ShipDtl.OrderRelNum = OrderRel.OrderRelNum
        INNER JOIN Erp.OrderHed AS OrderHed ON ShipDtl.Company = OrderHed.Company AND ShipDtl.OrderNum = OrderHed.OrderNum
        WHERE ShipHead.ReadyToInvoice = 1 AND OrderHed.OpenOrder = 0
          AND DATEPART(month, ShipHead.ShipDate) = :month AND DATEPART(year, ShipHead.ShipDate) = :year
          AND OrderRel.ReqDate IS NOT NULL
          AND ShipDtl.WarehouseCode NOT IN ('65','9000-CH','9000-CP')
          AND (CONVERT(int, CONVERT(datetime, ShipHead.ShipDate)) - CONVERT(int, CONVERT(datetime, OrderRel.ReqDate))) < 1
        GROUP BY ShipHead.Company
    ",

    /*
    |----------------------------------------------------------------------
    | Backlog query (secondary panel — open orders by required ship window)
    |----------------------------------------------------------------------
    */
    'backlog_query' => "
        SELECT
          OrderHed.Company AS company_code,
          COUNT(OrderHed.OpenOrder) AS open_orders,
          COUNT(CASE WHEN OrderRel.ReqDate <= CONVERT(date, GETDATE()-1) THEN 1 END) AS past_due,
          COUNT(CASE WHEN OrderRel.ReqDate =  CONVERT(date, GETDATE())   THEN 1 END) AS due_today,
          COUNT(CASE WHEN OrderRel.ReqDate >  CONVERT(date, GETDATE())    AND OrderRel.ReqDate <  CONVERT(date, GETDATE()+8)  THEN 1 END) AS d1_7,
          COUNT(CASE WHEN OrderRel.ReqDate >  CONVERT(date, GETDATE()+7)  AND OrderRel.ReqDate <= CONVERT(date, GETDATE()+14) THEN 1 END) AS d8_14,
          COUNT(CASE WHEN OrderRel.ReqDate >  CONVERT(date, GETDATE()+14) AND OrderRel.ReqDate <= CONVERT(date, GETDATE()+21) THEN 1 END) AS d15_21,
          COUNT(CASE WHEN OrderRel.ReqDate >  CONVERT(date, GETDATE()+21) AND OrderRel.ReqDate <= CONVERT(date, GETDATE()+28) THEN 1 END) AS d22_28,
          COUNT(CASE WHEN OrderRel.ReqDate >  CONVERT(date, GETDATE()+28) THEN 1 END) AS d29_plus
        FROM Erp.OrderHed AS OrderHed
        INNER JOIN Erp.OrderDtl AS OrderDtl ON OrderHed.Company = OrderDtl.Company AND OrderHed.OrderNum = OrderDtl.OrderNum AND OrderDtl.KitFlag <> 'C'
        INNER JOIN Erp.OrderRel AS OrderRel ON OrderDtl.Company = OrderRel.Company AND OrderDtl.OrderNum = OrderRel.OrderNum AND OrderDtl.OrderLine = OrderRel.OrderLine
        INNER JOIN Erp.Customer AS Customer ON OrderHed.Company = Customer.Company AND OrderHed.CustNum = Customer.CustNum
        WHERE OrderHed.OpenOrder = 1 AND OrderRel.NeedByDate IS NOT NULL
          AND (OrderRel.OurReqQty - OrderRel.OurJobShippedQty - OrderRel.OurStockShippedQty) > 0
        GROUP BY OrderHed.Company
    ",
];
